<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Dosen;
use App\Models\Instansi;
use App\Models\Mahasiswa;
use App\Models\Surat;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class SuratController extends Controller
{
    public function index(Request $request)
    {
        $permohonanMasuk = Surat::with('mahasiswa')
            ->where('penerima_role', 'admin')
            ->where('jenis', Surat::JENIS_PERMOHONAN)
            ->latest()->get();

        // ── Filter untuk tab "Surat Masuk" ──────────────────────────────────
        $searchMasuk = trim((string) $request->query('search_masuk'));
        $jenisMasuk  = $request->query('jenis_masuk');
        $statusMasuk = $request->query('status_masuk'); // dibalas / belum

        // Surat masuk non-permohonan (dari mahasiswa/dosen/instansi ke admin),
        // sekaligus muat seluruh riwayat balasannya (thread) secara berjenjang.
        $suratMasuk = Surat::with(['mahasiswa', 'balasan.mahasiswa', 'balasan.balasan.mahasiswa'])
            ->where('penerima_role', 'admin')
            ->where('jenis', '!=', Surat::JENIS_PERMOHONAN)
            ->when($searchMasuk !== '', function ($q) use ($searchMasuk) {
                $q->where(function ($qq) use ($searchMasuk) {
                    $qq->where('perihal', 'like', "%{$searchMasuk}%")
                        ->orWhere('keterangan', 'like', "%{$searchMasuk}%")
                        ->orWhereHas('mahasiswa', fn ($m) => $m->where('nama', 'like', "%{$searchMasuk}%"))
                        ->orWhereHas('balasan', function ($b) use ($searchMasuk) {
                            $b->where('perihal', 'like', "%{$searchMasuk}%")
                              ->orWhere('keterangan', 'like', "%{$searchMasuk}%");
                        });
                });
            })
            ->when($jenisMasuk, fn ($q) => $q->where('jenis', $jenisMasuk))
            ->when($statusMasuk === 'dibalas', fn ($q) => $q->whereHas('balasan'))
            ->when($statusMasuk === 'belum', fn ($q) => $q->whereDoesntHave('balasan'))
            ->latest()->get();

        // ── Filter untuk tab "Semua Riwayat" ────────────────────────────────
        $searchRiwayat = trim((string) $request->query('search_riwayat'));
        $jenisRiwayat  = $request->query('jenis_riwayat');
        $statusRiwayat = $request->query('status_riwayat');
        $dariRiwayat   = $request->query('dari_riwayat'); // filter pengirim_role

        // Semua riwayat lintas aktor untuk monitoring, termasuk data induk
        // supaya balasan bisa ditelusuri balik ke surat asalnya saat dicari.
        $semuaRiwayat = Surat::with(['mahasiswa', 'parent'])
            ->when($searchRiwayat !== '', function ($q) use ($searchRiwayat) {
                $q->where(function ($qq) use ($searchRiwayat) {
                    $qq->where('perihal', 'like', "%{$searchRiwayat}%")
                        ->orWhere('keterangan', 'like', "%{$searchRiwayat}%")
                        ->orWhereHas('mahasiswa', fn ($m) => $m->where('nama', 'like', "%{$searchRiwayat}%"));
                });
            })
            ->when($jenisRiwayat, fn ($q) => $q->where('jenis', $jenisRiwayat))
            ->when($statusRiwayat, fn ($q) => $q->where('status', $statusRiwayat))
            ->when($dariRiwayat, fn ($q) => $q->where('pengirim_role', $dariRiwayat))
            ->latest()->get();

        $listMahasiswa = Mahasiswa::orderBy('nama')->get();
        $listDosen     = Dosen::orderBy('nama')->get();
        $listInstansi  = Instansi::orderBy('nama')->get();

        return view('admin.surat.index', compact(
            'permohonanMasuk',
            'suratMasuk',
            'semuaRiwayat',
            'listMahasiswa',
            'listDosen',
            'listInstansi',
            'searchMasuk',
            'jenisMasuk',
            'statusMasuk',
            'searchRiwayat',
            'jenisRiwayat',
            'statusRiwayat',
            'dariRiwayat',
        ));
    }

    /**
     * Admin setujui permohonan & (opsional) upload file surat pengantar resmi.
     * Otomatis buat surat pengantar baru (admin → mahasiswa) supaya mahasiswa
     * bisa meneruskannya ke instansi.
     */
    public function approve(Request $request, Surat $surat)
    {
        abort_unless(
            $surat->jenis === Surat::JENIS_PERMOHONAN && $surat->status === Surat::STATUS_PENDING,
            422,
            'Permohonan ini sudah diproses.'
        );

        $validator = Validator::make($request->all(), [
            'file'       => 'nullable|file|extensions:pdf,doc,docx|max:10240',
            'keterangan' => 'nullable|string|max:1000',
        ], [
            'file.extensions' => 'File harus berformat PDF, DOC, atau DOCX.',
        ]);
        if ($validator->fails()) {
            return back()->withErrors($validator, 'approve')->withInput()->with('approve_id', $surat->id);
        }

        $path = null;
        if ($request->hasFile('file')) {
            $path = $request->file('file')->store('surat/' . $surat->mahasiswa_id, 'public');
        }

        $surat->update(['status' => Surat::STATUS_DISETUJUI]);

        Surat::create([
            'mahasiswa_id'  => $surat->mahasiswa_id,
            'pengirim_role' => 'admin',
            'pengirim_id'   => null,
            'penerima_role' => 'mahasiswa',
            'penerima_id'   => $surat->mahasiswa_id,
            'parent_id'     => $surat->id,
            'perihal'       => $surat->perihal,
            'jenis'         => Surat::JENIS_PENGANTAR,
            'keterangan'    => $request->input('keterangan'),
            'file'          => $path,
            'status'        => Surat::STATUS_TERKIRIM,
        ]);

        return back()->with('success', 'Permohonan disetujui & surat pengantar dikirim ke mahasiswa.');
    }

    public function reject(Request $request, Surat $surat)
    {
        abort_unless(
            $surat->jenis === Surat::JENIS_PERMOHONAN && $surat->status === Surat::STATUS_PENDING,
            422,
            'Permohonan ini sudah diproses.'
        );

        $request->validate(
            ['catatan' => 'required|string|max:500'],
            ['catatan.required' => 'Alasan penolakan wajib diisi.']
        );

        $surat->update(['status' => Surat::STATUS_DITOLAK, 'catatan' => $request->catatan]);

        return back()->with('success', 'Permohonan surat ditolak.');
    }

    /**
     * Admin kirim surat bebas ke mahasiswa / dosen / instansi.
     */
    public function kirim(Request $request)
    {
        $kirimSemuaMahasiswa = $request->tujuan_role === 'mahasiswa' && $request->boolean('kirim_semua_mahasiswa');

        $rules = [
            'tujuan_role'         => 'required|in:mahasiswa,dosen,instansi',
            'tujuan_dosen_id'     => 'required_if:tujuan_role,dosen|nullable|exists:dosens,id',
            'tujuan_instansi_id'  => 'required_if:tujuan_role,instansi|nullable|exists:instansis,id',
            'perihal'             => 'required|string|max:255',
            'keterangan'          => 'required|string|max:2000',
            'file'                => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:10240',
        ];

        // Mahasiswa tujuan hanya wajib dipilih kalau bukan mode "kirim ke semua".
        if (!$kirimSemuaMahasiswa) {
            $rules['tujuan_mahasiswa_id'] = 'required_if:tujuan_role,mahasiswa|nullable|exists:mahasiswas,id';
        }

        $validator = Validator::make($request->all(), $rules, [
            'tujuan_mahasiswa_id.required_if' => 'Pilih mahasiswa tujuan.',
            'tujuan_dosen_id.required_if'     => 'Pilih dosen tujuan.',
            'tujuan_instansi_id.required_if'  => 'Pilih instansi tujuan.',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator, 'kirim')->withInput();
        }

        // ── Mode broadcast: kirim ke semua mahasiswa sekaligus ──────────────
        if ($kirimSemuaMahasiswa) {
            $listMahasiswa = Mahasiswa::all();

            if ($listMahasiswa->isEmpty()) {
                return back()->withErrors(['kirim' => 'Belum ada data mahasiswa.'], 'kirim')->withInput();
            }

            $path = null;
            if ($request->hasFile('file')) {
                $path = $request->file('file')->store('surat/admin', 'public');
            }

            foreach ($listMahasiswa as $m) {
                Surat::create([
                    'mahasiswa_id'  => $m->id,
                    'pengirim_role' => 'admin',
                    'pengirim_id'   => null,
                    'penerima_role' => 'mahasiswa',
                    'penerima_id'   => $m->id,
                    'perihal'       => $request->perihal,
                    'jenis'         => Surat::JENIS_UMUM,
                    'keterangan'    => $request->keterangan,
                    'file'          => $path,
                    'status'        => Surat::STATUS_TERKIRIM,
                ]);
            }

            return back()->with('success', "Surat berhasil dikirim ke {$listMahasiswa->count()} mahasiswa.");
        }

        [$penerimaRole, $penerimaId, $mahasiswaId] = match ($request->tujuan_role) {
            'mahasiswa' => ['mahasiswa', (int) $request->tujuan_mahasiswa_id, (int) $request->tujuan_mahasiswa_id],
            'dosen'     => ['dosen',     (int) $request->tujuan_dosen_id,     null],
            'instansi'  => ['instansi',  (int) $request->tujuan_instansi_id,  null],
        };

        $path = null;
        if ($request->hasFile('file')) {
            $folder = $mahasiswaId ? 'surat/' . $mahasiswaId : 'surat/admin';
            $path   = $request->file('file')->store($folder, 'public');
        }

        Surat::create([
            'mahasiswa_id'  => $mahasiswaId,
            'pengirim_role' => 'admin',
            'pengirim_id'   => null,
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
     * Admin balas surat masuk dari mahasiswa / dosen / instansi.
     */
    public function balas(Request $request, Surat $surat)
    {
        abort_unless($surat->penerima_role === 'admin', 403);

        $validator = Validator::make($request->all(), [
            'keterangan' => 'required|string|max:2000',
            'file'       => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:10240',
        ]);
        if ($validator->fails()) {
            return back()->withErrors($validator, 'balas')->withInput()->with('balas_id', $surat->id);
        }

        $path = null;
        if ($request->hasFile('file')) {
            $folder = $surat->mahasiswa_id ? 'surat/' . $surat->mahasiswa_id : 'surat/admin';
            $path   = $request->file('file')->store($folder, 'public');
        }

        Surat::create([
            'mahasiswa_id'  => $surat->mahasiswa_id,
            'pengirim_role' => 'admin',
            'pengirim_id'   => null,
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
}