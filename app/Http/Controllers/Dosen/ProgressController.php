<?php

namespace App\Http\Controllers\Dosen;

use App\Http\Controllers\Controller;
use App\Models\Mahasiswa;
use App\Models\ProgressBab;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProgressController extends Controller
{
    public function index()
    {
        $dosen      = Auth::user()->dosen;
        $mahasiswas = Mahasiswa::with(['progressBabs', 'instansi'])
                        ->where('dosen_id', $dosen->id)->get();

        return view('dosen.progress.index', compact('mahasiswas'));
    }

    public function update(Request $request, ProgressBab $progressBab)
    {
        // Pastikan BAB ini milik mahasiswa bimbingan dosen ini
        $dosen = Auth::user()->dosen;
        $mhs   = $progressBab->mahasiswa;

        abort_if($mhs->dosen_id !== $dosen->id, 403);

        $request->validate([
            'status'         => 'required|in:belum,proses,selesai',
            'tanggal_update' => 'nullable|date',
            'catatan'        => 'nullable|string|max:500',
        ]);

        $progressBab->update($request->only(['status', 'tanggal_update', 'catatan']));

        return back()->with('success', 'Progress BAB berhasil diperbarui.');
    }
}