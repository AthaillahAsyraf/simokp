<?php

namespace App\Http\Controllers\Instansi;

use App\Http\Controllers\Controller;
use App\Models\Surat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class SuratController extends Controller
{
    public function index()
    {
        $instansi = Auth::user()->instansi;

        $suratMasuk = Surat::with('mahasiswa')
            ->where('penerima_role', 'instansi')->where('penerima_id', $instansi->id)
            ->latest()->get();

        return view('instansi.surat.index', compact('suratMasuk'));
    }

    /** Instansi membalas surat pengantar yang diterima dari mahasiswa */
    public function balas(Request $request, Surat $surat)
    {
        $instansi = Auth::user()->instansi;
        abort_if($surat->penerima_role !== 'instansi' || $surat->penerima_id !== $instansi->id, 403);

        $validator = Validator::make($request->all(), [
            'keterangan' => 'required|string|max:1000',
            'file'       => 'nullable|file|mimes:pdf,doc,docx|max:10240',
        ]);
        if ($validator->fails()) {
            return back()->withErrors($validator, 'balas')->withInput()->with('balas_id', $surat->id);
        }

        $path = null;
        if ($request->hasFile('file')) {
            $path = $request->file('file')->store('surat/'.$surat->mahasiswa_id, 'public');
        }

        Surat::create([
            'mahasiswa_id'   => $surat->mahasiswa_id,
            'pengirim_role'  => 'instansi',
            'pengirim_id'    => $instansi->id,
            'penerima_role'  => 'mahasiswa',
            'penerima_id'    => $surat->mahasiswa_id,
            'parent_id'      => $surat->id,
            'perihal'        => 'Balasan: '.$surat->perihal,
            'jenis'          => Surat::JENIS_BALASAN,
            'keterangan'     => $request->keterangan,
            'file'           => $path,
            'status'         => Surat::STATUS_TERKIRIM,
        ]);

        return back()->with('success', 'Balasan surat berhasil dikirim ke mahasiswa.');
    }
}