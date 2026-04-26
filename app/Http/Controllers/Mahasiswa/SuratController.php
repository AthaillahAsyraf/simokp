<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\Surat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SuratController extends Controller
{
    public function index()
    {
        $mahasiswa = Auth::user()->mahasiswa;
        $surats    = Surat::where('mahasiswa_id', $mahasiswa->id)->latest()->get();
        return view('mahasiswa.surat.index', compact('mahasiswa', 'surats'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'jenis'      => 'required|in:permohonan,keterangan,pengantar',
            'keterangan' => 'nullable|string|max:500',
        ]);

        $mahasiswa = Auth::user()->mahasiswa;

        Surat::create([
            'mahasiswa_id' => $mahasiswa->id,
            'jenis'        => $request->jenis,
            'keterangan'   => $request->keterangan,
            'status'       => 'pending',
        ]);

        return back()->with('success', 'Permohonan surat berhasil dikirim. Menunggu persetujuan admin.');
    }
}