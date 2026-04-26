<?php

namespace App\Http\Controllers\Instansi;

use App\Http\Controllers\Controller;
use App\Models\Mahasiswa;
use App\Models\Nilai;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NilaiController extends Controller
{
    public function index()
    {
        $instansi   = Auth::user()->instansi;
        $mahasiswas = Mahasiswa::with('nilai')
                        ->where('instansi_id', $instansi->id)->get();

        return view('instansi.nilai.index', compact('mahasiswas'));
    }

    public function update(Request $request, Mahasiswa $mahasiswa)
    {
        $instansi = Auth::user()->instansi;
        abort_if($mahasiswa->instansi_id !== $instansi->id, 403);

        $request->validate([
            'nilai_instansi'   => 'required|numeric|min:0|max:100',
            'catatan_instansi' => 'nullable|string|max:500',
        ]);

        $nilai = Nilai::firstOrCreate(['mahasiswa_id' => $mahasiswa->id]);
        $nilai->nilai_instansi   = $request->nilai_instansi;
        $nilai->catatan_instansi = $request->catatan_instansi;
        $nilai->nilai_akhir      = $nilai->hitungNilaiAkhir();
        $nilai->save();

        return back()->with('success', 'Nilai instansi berhasil disimpan.');
    }
}