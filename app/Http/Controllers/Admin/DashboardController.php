<?php
// ─── Admin/DashboardController.php ──────────────────────────────────────────
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\{Mahasiswa, Dosen, Instansi, Seminar};

class DashboardController extends Controller {
    public function index() {
        $stats = [
            'total'    => Mahasiswa::count(),
            'proses'   => Mahasiswa::where('status','proses')->count(),
            'seminar'  => Mahasiswa::where('status','seminar')->count(),
            'selesai'  => Mahasiswa::where('status','selesai')->count(),
            'dosen'    => Dosen::count(),
            'instansi' => Instansi::count(),
        ];
        $mahasiswas = Mahasiswa::with(['dosen','instansi','progressBabs'])->latest()->take(6)->get();
        $seminars   = Seminar::with('mahasiswa')->orderBy('tanggal')->take(5)->get();
        return view('admin.dashboard', compact('stats','mahasiswas','seminars'));
    }
}