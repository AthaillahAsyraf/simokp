<?php

namespace App\Http\Controllers\Dosen;

use App\Http\Controllers\Controller;
use App\Models\Mahasiswa;
use App\Models\Nilai;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NilaiController extends Controller
{
    public function index()
    {
        $dosen      = Auth::user()->dosen;
        $mahasiswas = Mahasiswa::with(['nilai', 'seminar'])
                        ->where('dosen_id', $dosen->id)->get();

        return view('dosen.nilai.index', compact('mahasiswas'));
    }

    public function update(Request $request, Mahasiswa $mahasiswa)
    {
        $dosen = Auth::user()->dosen;
        abort_if($mahasiswa->dosen_id !== $dosen->id, 403);

        $request->validate([
            'nilai_pembimbing'   => 'required|numeric|min:0|max:100',
            'catatan_pembimbing' => 'nullable|string|max:500',
        ]);

        $nilai = Nilai::firstOrCreate(['mahasiswa_id' => $mahasiswa->id]);
        $nilai->nilai_pembimbing   = $request->nilai_pembimbing;
        $nilai->catatan_pembimbing = $request->catatan_pembimbing;
        $nilai->nilai_akhir        = $nilai->hitungNilaiAkhir();
        $nilai->save();

        return back()->with('success', 'Nilai pembimbingan berhasil disimpan.');
    }

    public function updateSeminar(Request $request, Mahasiswa $mahasiswa)
    {
        $dosen = Auth::user()->dosen;
        abort_if($mahasiswa->dosen_id !== $dosen->id, 403);

        $request->validate(['nilai_seminar' => 'required|numeric|min:0|max:100']);

        $seminar = $mahasiswa->seminar;
        abort_if(!$seminar, 404, 'Jadwal seminar belum ada.');

        $seminar->update(['nilai' => $request->nilai_seminar, 'status' => 'hadir']);

        $nilai = Nilai::firstOrCreate(['mahasiswa_id' => $mahasiswa->id]);
        $nilai->nilai_seminar = $request->nilai_seminar;
        $nilai->nilai_akhir   = $nilai->hitungNilaiAkhir();
        $nilai->save();

        return back()->with('success', 'Nilai seminar berhasil disimpan.');
    }
}