<?php

namespace App\Http\Controllers\Dosen;

use App\Http\Controllers\Controller;
use App\Models\Instansi;
use App\Models\Mahasiswa;
use App\Models\Surat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class SuratController extends Controller
{
    private function dosen()
    {
        return Auth::user()->dosen;
    }

    public function index()
    {
        $dosen = $this->dosen();

        $suratMasuk = Surat::where('penerima_role', 'dosen')
            ->where('penerima_id', $dosen->id)
            ->with('balasan')
            ->latest()->get();

        $suratTerkirim = Surat::where('pengirim_role', 'dosen')
            ->where('pengirim_id', $dosen->id)
            ->latest()->get();

        // Hanya mahasiswa bimbingan dosen ini
        $listMahasiswa = Mahasiswa::where('dosen_id', $dosen->id)
            ->orderBy('nama')->get();

        $listInstansi = Instansi::orderBy('nama')->get();

        return view('dosen.surat.index', compact(
            'suratMasuk',
            'suratTerkirim',
            'listMahasiswa',
            'listInstansi',
        ));
    }

    /**
     * Dosen kirim surat bebas ke mahasiswa / admin / instansi.
     */
    public function kirim(Request $request)
    {
        $dosen = $this->dosen();

        $validator = Validator::make($request->all(), [
            'tujuan_role'         => 'required|in:mahasiswa,admin,instansi',
            'tujuan_mahasiswa_id' => 'required_if:tujuan_role,mahasiswa|nullable|exists:mahasiswas,id',
            'tujuan_instansi_id'  => 'required_if:tujuan_role,instansi|nullable|exists:instansis,id',
            'perihal'             => 'required|string|max:255',
            'keterangan'          => 'required|string|max:2000',
            'file'                => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:10240',
        ], [
            'tujuan_mahasiswa_id.required_if' => 'Pilih mahasiswa tujuan.',
            'tujuan_instansi_id.required_if'  => 'Pilih instansi tujuan.',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator, 'kirim')->withInput();
        }

        [$penerimaRole, $penerimaId, $mahasiswaId] = match ($request->tujuan_role) {
            'mahasiswa' => ['mahasiswa', (int) $request->tujuan_mahasiswa_id, (int) $request->tujuan_mahasiswa_id],
            'instansi'  => ['instansi',  (int) $request->tujuan_instansi_id,  null],
            'admin'     => ['admin',     null,                                 null],
        };

        $path = null;
        if ($request->hasFile('file')) {
            $folder = $mahasiswaId ? 'surat/' . $mahasiswaId : 'surat/dosen-' . $dosen->id;
            $path   = $request->file('file')->store($folder, 'public');
        }

        Surat::create([
            'mahasiswa_id'  => $mahasiswaId,
            'pengirim_role' => 'dosen',
            'pengirim_id'   => $dosen->id,
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
     * Dosen balas surat masuk.
     */
    public function balas(Request $request, Surat $surat)
    {
        $dosen = $this->dosen();
        abort_unless(
            $surat->penerima_role === 'dosen' && $surat->penerima_id === $dosen->id,
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
            $folder = $surat->mahasiswa_id ? 'surat/' . $surat->mahasiswa_id : 'surat/dosen-' . $dosen->id;
            $path   = $request->file('file')->store($folder, 'public');
        }

        Surat::create([
            'mahasiswa_id'  => $surat->mahasiswa_id,
            'pengirim_role' => 'dosen',
            'pengirim_id'   => $dosen->id,
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
