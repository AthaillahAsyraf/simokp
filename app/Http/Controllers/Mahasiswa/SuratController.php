<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\Surat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class SuratController extends Controller
{
    private function mahasiswa()
    {
        return Auth::user()->mahasiswa->load('instansi');
    }

    public function index()
    {
        $mahasiswa = $this->mahasiswa();

        // Semua surat milik mahasiswa ini
        $semuaSurat = Surat::where('mahasiswa_id', $mahasiswa->id)
            ->orWhere(fn ($q) => $q->where('penerima_role', 'mahasiswa')->where('penerima_id', $mahasiswa->id))
            ->orWhere(fn ($q) => $q->where('pengirim_role', 'mahasiswa')->where('pengirim_id', $mahasiswa->id))
            ->with('parent', 'balasan')
            ->latest()
            ->get()
            ->unique('id');

        $adaPending = $semuaSurat->contains(
            fn ($s) => $s->jenis === Surat::JENIS_PERMOHONAN && $s->status === Surat::STATUS_PENDING
        );

        // Surat pengantar dari admin yang belum diteruskan ke instansi
        $pengantarBelumDiteruskan = $semuaSurat
            ->where('jenis', Surat::JENIS_PENGANTAR)
            ->where('penerima_role', 'mahasiswa')
            ->first(fn ($s) => ! $s->sudahDiteruskan());

        // Surat masuk (dikirim ke mahasiswa, bukan permohonan sendiri)
        $suratMasuk = $semuaSurat->filter(
            fn ($s) => $s->penerima_role === 'mahasiswa'
                && $s->penerima_id === $mahasiswa->id
                && $s->jenis !== Surat::JENIS_PERMOHONAN
        )->values();

        // Surat terkirim oleh mahasiswa
        $suratTerkirim = $semuaSurat->filter(
            fn ($s) => $s->pengirim_role === 'mahasiswa' && $s->pengirim_id === $mahasiswa->id
        )->values();

        // Surat pengantar (untuk bagian khusus di tab masuk)
        $suratPengantar = $semuaSurat->where('jenis', Surat::JENIS_PENGANTAR)
            ->where('penerima_role', 'mahasiswa')
            ->values();

        return view('mahasiswa.surat.index', compact(
            'mahasiswa',
            'adaPending',
            'pengantarBelumDiteruskan',
            'suratMasuk',
            'suratTerkirim',
            'suratPengantar',
        ));
    }

    /**
     * Mahasiswa minta dibuatkan surat pengantar oleh admin.
     * (route POST mahasiswa/surat — name: mahasiswa.surat.store)
     */
    public function store(Request $request)
    {
        $mahasiswa = $this->mahasiswa();

        $adaPending = Surat::where('mahasiswa_id', $mahasiswa->id)
            ->where('jenis', Surat::JENIS_PERMOHONAN)
            ->where('status', Surat::STATUS_PENDING)
            ->exists();

        if ($adaPending) {
            return back()->with('error', 'Anda masih punya permohonan surat yang belum diproses admin.');
        }

        $validator = Validator::make($request->all(), [
            'perihal'    => 'required|string|max:150',
            'keterangan' => 'required|string|max:1000',
        ]);
        if ($validator->fails()) {
            return back()->withErrors($validator, 'permohonan')->withInput();
        }

        Surat::create([
            'mahasiswa_id'  => $mahasiswa->id,
            'pengirim_role' => 'mahasiswa',
            'pengirim_id'   => $mahasiswa->id,
            'penerima_role' => 'admin',
            'penerima_id'   => null,
            'perihal'       => $request->perihal,
            'jenis'         => Surat::JENIS_PERMOHONAN,
            'keterangan'    => $request->keterangan,
            'status'        => Surat::STATUS_PENDING,
        ]);

        return back()->with('success', 'Permohonan surat terkirim, menunggu diproses admin.');
    }

    /**
     * Mahasiswa kirim surat bebas ke admin / dosen / instansi.
     * (route POST mahasiswa/surat/kirim — name: mahasiswa.surat.kirim)
     */
    public function kirim(Request $request)
    {
        $mahasiswa = $this->mahasiswa();

        $validator = Validator::make($request->all(), [
            'tujuan_role' => 'required|in:admin,dosen,instansi',
            'perihal'     => 'required|string|max:255',
            'keterangan'  => 'required|string|max:2000',
            'file'        => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:10240',
        ]);
        if ($validator->fails()) {
            return back()->withErrors($validator, 'kirim')->withInput();
        }

        [$penerimaRole, $penerimaId] = match ($request->tujuan_role) {
            'admin'    => ['admin',    null],
            'dosen'    => ['dosen',    $mahasiswa->dosen_id],
            'instansi' => ['instansi', $mahasiswa->instansi_id],
        };

        if (($request->tujuan_role === 'dosen' && ! $mahasiswa->dosen_id)
            || ($request->tujuan_role === 'instansi' && ! $mahasiswa->instansi_id)) {
            return back()->with('error', 'Anda belum memiliki ' . $request->tujuan_role . ' yang terdaftar.');
        }

        $path = null;
        if ($request->hasFile('file')) {
            $path = $request->file('file')->store('surat/' . $mahasiswa->id, 'public');
        }

        Surat::create([
            'mahasiswa_id'  => $mahasiswa->id,
            'pengirim_role' => 'mahasiswa',
            'pengirim_id'   => $mahasiswa->id,
            'penerima_role' => $penerimaRole,
            'penerima_id'   => $penerimaId,
            'perihal'       => $request->perihal,
            'jenis'         => Surat::JENIS_UMUM,
            'keterangan'    => $request->keterangan,
            'file'          => $path,
            'status'        => Surat::STATUS_TERKIRIM,
        ]);

        return back()->with('success', 'Surat berhasil dikirim.');
    }

    /**
     * Mahasiswa balas surat masuk (dari admin/dosen/instansi).
     * (route POST mahasiswa/surat/{surat}/balas — name: mahasiswa.surat.balas)
     */
    public function balas(Request $request, Surat $surat)
    {
        $mahasiswa = $this->mahasiswa();
        abort_unless(
            $surat->penerima_role === 'mahasiswa' && $surat->penerima_id === $mahasiswa->id,
            403
        );

        $validator = Validator::make($request->all(), [
            'keterangan' => 'required|string|max:2000',
            'file'       => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:10240',
        ]);
        if ($validator->fails()) {
            return back()->withErrors($validator, 'balas')->withInput()->with('balas_id', $surat->id);
        }

        $path = null;
        if ($request->hasFile('file')) {
            $path = $request->file('file')->store('surat/' . $mahasiswa->id, 'public');
        }

        Surat::create([
            'mahasiswa_id'  => $mahasiswa->id,
            'pengirim_role' => 'mahasiswa',
            'pengirim_id'   => $mahasiswa->id,
            'penerima_role' => $surat->pengirim_role,
            'penerima_id'   => $surat->pengirim_id,
            'parent_id'     => $surat->id,
            'perihal'       => 'Balasan: ' . $surat->perihal,
            'jenis'         => Surat::JENIS_BALASAN,
            'keterangan'    => $request->keterangan,
            'file'          => $path,
            'status'        => Surat::STATUS_TERKIRIM,
        ]);

        return back()->with('success', 'Balasan berhasil dikirim.');
    }

    /**
     * Teruskan surat pengantar (dari admin) ke instansi tempat KP.
     * (route POST mahasiswa/surat/{surat}/teruskan — name: mahasiswa.surat.teruskan)
     */
    public function teruskan(Request $request, Surat $surat)
    {
        $mahasiswa = $this->mahasiswa();
        abort_if($surat->mahasiswa_id !== $mahasiswa->id, 403);
        abort_if(
            $surat->jenis !== Surat::JENIS_PENGANTAR || $surat->penerima_role !== 'mahasiswa',
            422,
            'Surat ini bukan pengantar yang bisa diteruskan.'
        );

        if (! $mahasiswa->instansi_id) {
            return back()->with('error', 'Anda belum terdaftar di instansi, belum bisa meneruskan surat.');
        }
        if ($surat->sudahDiteruskan()) {
            return back()->with('error', 'Surat ini sudah pernah diteruskan ke instansi.');
        }

        Surat::create([
            'mahasiswa_id'  => $mahasiswa->id,
            'pengirim_role' => 'mahasiswa',
            'pengirim_id'   => $mahasiswa->id,
            'penerima_role' => 'instansi',
            'penerima_id'   => $mahasiswa->instansi_id,
            'parent_id'     => $surat->id,
            'perihal'       => $surat->perihal,
            'jenis'         => Surat::JENIS_PENGANTAR,
            'keterangan'    => $request->input('catatan_teruskan'),
            'file'          => $surat->file,
            'status'        => Surat::STATUS_TERKIRIM,
        ]);

        return back()->with('success', "Surat pengantar berhasil diteruskan ke {$mahasiswa->instansi->nama}.");
    }
}
