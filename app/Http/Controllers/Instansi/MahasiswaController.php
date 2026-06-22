<?php
namespace App\Http\Controllers\Instansi;
use App\Http\Controllers\Controller;
use App\Models\Mahasiswa;
use Illuminate\Support\Facades\Auth;

class MahasiswaController extends Controller {
    public function index() {
        $instansi   = Auth::user()->instansi;
        $mahasiswas = $instansi->mahasiswas()->with(['dosen','progressBabs','seminar'])->get();
        return view('instansi.mahasiswa.index', compact('instansi','mahasiswas'));
    }
    public function show(Mahasiswa $mahasiswa) {
        $instansi = Auth::user()->instansi;
        abort_if($mahasiswa->instansi_id !== $instansi->id, 403);
        $mahasiswa->load(['dosen','progressBabs','seminar']);
        return view('instansi.mahasiswa.show', compact('mahasiswa'));
    }
}