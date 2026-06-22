<?php
namespace App\Http\Controllers\Dosen;
use App\Http\Controllers\Controller;
use App\Models\ProgressBab;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProgressController extends Controller {
    public function index() {
        $dosen      = Auth::user()->dosen;
        $mahasiswas = $dosen->mahasiswas()->with(['progressBabs','instansi'])->get();
        return view('dosen.progress.index', compact('mahasiswas'));
    }

    public function update(Request $request, ProgressBab $progressBab) {
        $dosen = Auth::user()->dosen;
        abort_if($progressBab->mahasiswa->dosen_id !== $dosen->id, 403);
        $request->validate(['status'=>'required|in:belum,selesai','catatan'=>'nullable|string|max:500']);

        $mahasiswa = $progressBab->mahasiswa;
        $urutan    = $progressBab->urutan();

        if ($request->status === 'selesai') {
            $mahasiswa->selesaikanSampaiUrutan($urutan, $request->catatan);
        } else {
            $mahasiswa->resetDariBab($urutan);
        }
        return back()->with('success',"Progress {$progressBab->bab} berhasil diperbarui.");
    }
}