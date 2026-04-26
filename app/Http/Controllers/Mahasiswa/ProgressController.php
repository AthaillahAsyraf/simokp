<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\ProgressBab;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProgressController extends Controller
{
    public function index()
    {
        $mahasiswa = Auth::user()->mahasiswa->load('progressBabs');
        return view('mahasiswa.progress.index', compact('mahasiswa'));
    }

    public function update(Request $request, ProgressBab $progressBab)
    {
        $mahasiswa = Auth::user()->mahasiswa;
        abort_if($progressBab->mahasiswa_id !== $mahasiswa->id, 403);

        $request->validate([
            'status'         => 'required|in:belum,proses,selesai',
            'tanggal_update' => 'nullable|date',
            'catatan'        => 'nullable|string|max:500',
        ]);

        $progressBab->update($request->only(['status', 'tanggal_update', 'catatan']));

        return back()->with('success', 'Progress BAB berhasil diperbarui.');
    }
}