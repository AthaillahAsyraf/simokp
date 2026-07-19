<?php

namespace App\Http\Controllers\Instansi;

use App\Http\Controllers\Controller;
use App\Models\Dosen;
use App\Models\Mahasiswa;
use App\Models\Surat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class SuratController extends Controller
{
    private function instansi()
    {
        return Auth::user()->instansi;
    }

    public function index()
    {
        $instansi = $this->instansi();

        $suratMasuk = Surat::with('mahasiswa', 'balasan')
            ->where('penerima_role', 'instansi')
            ->where('penerima_id', $instansi->id)
            ->latest()->get();

        $suratTerkirim = Surat::where('pengirim_role', 'instansi')
            ->where('pengirim_id', $instansi->id)
            ->latest()->get();

        // Hanya mahasiswa yang terdaftar di instansi ini
        $listMahasiswa = Mahasiswa::where('instansi_id', $instansi->id)
            ->orderBy('nama')->get();

        $listDosen = Dosen::orderBy('nama')->get();

        return view('instansi.surat.index', compact(
            'suratMasuk',
            'suratTerkirim',
            'listMahasiswa',
            'listDosen',
        ));
    }

    /**
     * Instansi balas surat masuk (dari mahasiswa).
     */
    public function balas(Request $request, Surat $surat)
    {
        $instansi = $this->instansi();
        abort_if(
            $surat->penerima_role !== 'instansi' || $surat->penerima_id !== $instansi->id,
            403
        );

        $validator = Validator::make($request->all(), [
            'keterangan' => 'required|string|max:1000',
            'file'       => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:10240',
        ]);
        if ($validator->fails()) {
            return back()->withErrors($validator, 'balas')->withInput()->with('balas_id', $surat->id);
        }

        $path = null;
        if ($request->hasFile('file')) {
            $path = $request->file('file')->store('surat/' . $surat->mahasiswa_id, 'public');
        }

        Surat::create([
            'mahasiswa_id'  => $surat->mahasiswa_id,
            'pengirim_role' => 'instansi',
            'pengirim_id'   => $instansi->id,
            'penerima_role' => $surat->pengirim_role,
            'penerima_id'   => $surat->pengirim_id,
            'parent_id'     => $surat->id,
            'perihal'       => 'Balasan: ' . $surat->perihal,
            'jenis'         => Surat::JENIS_BALASAN,
            'keterangan'    => $request->keterangan,
            'file'          => $path,
            'status'        => Surat::STATUS_TERKIRIM,
        ]);

        return back()->with('success', 'Balasan surat berhasil dikirim.');
    }

    /**
     * Instansi kirim surat bebas ke mahasiswa / admin / dosen.
     */
    public function kirim(Request $request)
    {
        $instansi = $this->instansi();

        $validator = Validator::make($request->all(), [
            'tujuan_role'         => 'required|in:mahasiswa,admin,dosen',
            'tujuan_mahasiswa_id' => 'required_if:tujuan_role,mahasiswa|nullable|exists:mahasiswas,id',
            'tujuan_dosen_id'     => 'required_if:tujuan_role,dosen|nullable|exists:dosens,id',
            'perihal'             => 'required|string|max:255',
            'keterangan'          => 'required|string|max:2000',
            'file'                => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:10240',
        ], [
            'tujuan_mahasiswa_id.required_if' => 'Pilih mahasiswa tujuan.',
            'tujuan_dosen_id.required_if'     => 'Pilih dosen tujuan.',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator, 'kirim')->withInput();
        }

        [$penerimaRole, $penerimaId, $mahasiswaId] = match ($request->tujuan_role) {
            'mahasiswa' => ['mahasiswa', (int) $request->tujuan_mahasiswa_id, (int) $request->tujuan_mahasiswa_id],
            'dosen'     => ['dosen',     (int) $request->tujuan_dosen_id,     null],
            'admin'     => ['admin',     null,                                 null],
        };

        $path = null;
        if ($request->hasFile('file')) {
            $folder = $mahasiswaId ? 'surat/' . $mahasiswaId : 'surat/instansi-' . $instansi->id;
            $path   = $request->file('file')->store($folder, 'public');
        }

        Surat::create([
            'mahasiswa_id'  => $mahasiswaId,
            'pengirim_role' => 'instansi',
            'pengirim_id'   => $instansi->id,
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
}
