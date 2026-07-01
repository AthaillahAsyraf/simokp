<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\Surat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class SuratController extends Controller
{
    public function index()
    {
        $mahasiswa = Auth::user()->mahasiswa->load('instansi');
        $surats    = Surat::where('mahasiswa_id', $mahasiswa->id)->with('parent')->latest()->get();

        $adaPending = $surats->contains(fn ($s) => $s->jenis === Surat::JENIS_PERMOHONAN && $s->status === Surat::STATUS_PENDING);

        // pengantar yang sudah disetujui admin (dikirim ke mahasiswa) tapi belum diteruskan ke instansi
        $pengantarBelumDiteruskan = $surats
            ->where('jenis', Surat::JENIS_PENGANTAR)
            ->where('penerima_role', 'mahasiswa')
            ->first(fn ($s) => !$s->sudahDiteruskan());

        return view('mahasiswa.surat.index', compact('mahasiswa', 'surats', 'adaPending', 'pengantarBelumDiteruskan'));
    }

    /** Mahasiswa minta dibuatkan surat pengantar oleh admin */
    public function store(Request $request)
    {
        $mahasiswa = Auth::user()->mahasiswa;

        $adaPending = Surat::where('mahasiswa_id', $mahasiswa->id)
            ->where('jenis', Surat::JENIS_PERMOHONAN)->where('status', Surat::STATUS_PENDING)->exists();
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
            'mahasiswa_id'   => $mahasiswa->id,
            'pengirim_role'  => 'mahasiswa',
            'pengirim_id'    => $mahasiswa->id,
            'penerima_role'  => 'admin',
            'penerima_id'    => null,
            'perihal'        => $request->perihal,
            'jenis'          => Surat::JENIS_PERMOHONAN,
            'keterangan'     => $request->keterangan,
            'status'         => Surat::STATUS_PENDING,
        ]);

        return back()->with('success', 'Permohonan surat terkirim, menunggu diproses admin.');
    }

    /** Teruskan surat pengantar (dari admin) ke instansi tempat KP */
    public function teruskan(Request $request, Surat $surat)
    {
        $mahasiswa = Auth::user()->mahasiswa;
        abort_if($surat->mahasiswa_id !== $mahasiswa->id, 403);
        abort_if($surat->jenis !== Surat::JENIS_PENGANTAR || $surat->penerima_role !== 'mahasiswa', 422, 'Surat ini bukan pengantar yang bisa diteruskan.');

        if (!$mahasiswa->instansi_id) {
            return back()->with('error', 'Anda belum terdaftar di instansi manapun, belum bisa meneruskan surat.');
        }
        if ($surat->sudahDiteruskan()) {
            return back()->with('error', 'Surat ini sudah pernah diteruskan ke instansi.');
        }

        Surat::create([
            'mahasiswa_id'   => $mahasiswa->id,
            'pengirim_role'  => 'mahasiswa',
            'pengirim_id'    => $mahasiswa->id,
            'penerima_role'  => 'instansi',
            'penerima_id'    => $mahasiswa->instansi_id,
            'parent_id'      => $surat->id,
            'perihal'        => $surat->perihal,
            'jenis'          => Surat::JENIS_PENGANTAR,
            'keterangan'     => $request->input('catatan_teruskan'),
            'file'           => $surat->file,
            'status'         => Surat::STATUS_TERKIRIM,
        ]);

        return back()->with('success', "Surat pengantar berhasil diteruskan ke {$mahasiswa->instansi->nama}.");
    }
}