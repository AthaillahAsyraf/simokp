<?php

namespace App\Http\Controllers\Instansi;

use App\Http\Controllers\Controller;
use App\Models\Mahasiswa;
use App\Models\Nilai;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class NilaiController extends Controller
{
    public function index()
    {
        $instansi   = Auth::user()->instansi;
        $mahasiswas = Mahasiswa::with(['nilai', 'dosen'])
                        ->where('instansi_id', $instansi->id)->get();

        return view('instansi.nilai.index', compact('mahasiswas'));
    }

    public function update(Request $request, Mahasiswa $mahasiswa)
    {
        $instansi = Auth::user()->instansi;
        abort_if($mahasiswa->instansi_id !== $instansi->id, 403);

        $validator = Validator::make($request->all(), [
            'nilai_instansi'   => 'required|numeric|min:0|max:100',
            'catatan_instansi' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator, 'instansi')->withInput();
        }

        $nilai = Nilai::firstOrCreate(['mahasiswa_id' => $mahasiswa->id]);
        $nilai->nilai_instansi   = $request->nilai_instansi;
        $nilai->catatan_instansi = $request->catatan_instansi;
        $nilai->nilai_akhir      = $nilai->hitungNilaiAkhir();
        $nilai->save();

        return back()->with('success', 'Nilai instansi berhasil disimpan.');
    }
}