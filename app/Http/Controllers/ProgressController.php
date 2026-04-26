<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Mahasiswa;
use App\Models\ProgressBab;
use Illuminate\Http\Request;

class ProgressController extends Controller
{
    public function index()
    {
        $mahasiswas = Mahasiswa::with(['progressBabs', 'instansi', 'dosen'])->get();
        return view('admin.progress.index', compact('mahasiswas'));
    }

    public function update(Request $request, ProgressBab $progressBab)
    {
        $request->validate([
            'status'        => 'required|in:belum,proses,selesai',
            'tanggal_update'=> 'nullable|date',
            'catatan'       => 'nullable|string|max:500',
        ]);

        $progressBab->update($request->only(['status', 'tanggal_update', 'catatan']));

        return back()->with('success', 'Progress BAB berhasil diperbarui.');
    }
}