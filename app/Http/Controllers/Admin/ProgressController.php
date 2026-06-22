<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\{Mahasiswa, ProgressBab};
use Illuminate\Http\Request;

class ProgressController extends Controller {

    public function index(Request $request) {
        $query = Mahasiswa::with(['progressBabs','instansi','dosen']);
        if ($request->filled('status'))   $query->where('status',$request->status);
        if ($request->filled('instansi')) $query->where('instansi_id',$request->instansi);
        $mahasiswas = $query->latest()->get();
        return view('admin.progress.index', compact('mahasiswas'));
    }

    /**
     * Update BAB dengan cascade otomatis:
     * - Jika selesai → semua BAB sebelumnya ikut selesai
     * - Jika belum   → BAB ini & setelahnya di-reset ke belum
     */
    public function update(Request $request, ProgressBab $progressBab) {
        $request->validate([
            'status'  => 'required|in:belum,selesai',
            'catatan' => 'nullable|string|max:500',
        ]);

        $mahasiswa = $progressBab->mahasiswa;
        $urutan    = $progressBab->urutan();

        if ($request->status === 'selesai') {
            // Tandai BAB ini + semua sebelumnya selesai
            $mahasiswa->selesaikanSampaiUrutan($urutan, $request->catatan);
        } else {
            // Reset BAB ini + semua sesudahnya ke belum
            $mahasiswa->resetDariBab($urutan);
        }

        return back()->with('success', "Progress {$progressBab->bab} berhasil diperbarui (cascade otomatis).");
    }
}