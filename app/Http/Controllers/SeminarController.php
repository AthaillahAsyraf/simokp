<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Seminar;
use App\Models\Mahasiswa;
use App\Models\Nilai;
use Illuminate\Http\Request;

class SeminarController extends Controller
{
    public function index()
    {
        $seminars   = Seminar::with('mahasiswa')->orderBy('tanggal', 'asc')->get();
        $mahasiswas = Mahasiswa::where('status', 'seminar')->get();
        return view('admin.seminar.index', compact('seminars', 'mahasiswas'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'mahasiswa_id'  => 'required|exists:mahasiswas,id',
            'tanggal'       => 'required|date',
            'jam'           => 'required',
            'ruangan'       => 'required',
            'dosen_penguji' => 'nullable|string',
        ]);

        Seminar::updateOrCreate(
            ['mahasiswa_id' => $request->mahasiswa_id],
            $request->only(['tanggal', 'jam', 'ruangan', 'dosen_penguji'])
        );

        return back()->with('success', 'Jadwal seminar berhasil disimpan.');
    }

    public function update(Request $request, Seminar $seminar)
    {
        $request->validate([
            'status' => 'required|in:terjadwal,hadir,tidak_hadir',
            'nilai'  => 'nullable|numeric|min:0|max:100',
        ]);

        $seminar->update($request->only(['status', 'nilai', 'catatan', 'tanggal', 'jam', 'ruangan', 'dosen_penguji']));

        // Sinkron nilai seminar ke tabel nilais
        if ($request->filled('nilai')) {
            $nilai = Nilai::firstOrCreate(['mahasiswa_id' => $seminar->mahasiswa_id]);
            $nilai->nilai_seminar = $request->nilai;
            $nilai->nilai_akhir   = $nilai->hitungNilaiAkhir();
            $nilai->save();
        }

        return back()->with('success', 'Data seminar diperbarui.');
    }
}