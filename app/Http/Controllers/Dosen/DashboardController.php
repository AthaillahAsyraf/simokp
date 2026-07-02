<?php
// ─── Dosen/DashboardController.php ──────────────────────────────────────────
namespace App\Http\Controllers\Dosen;

use App\Http\Controllers\Controller;
use App\Models\Seminar;
use App\Models\ProgressBab; // ← sesuaikan kalau nama model kamu beda
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller {
    public function index() {
        $dosen      = Auth::user()->dosen;
        $mahasiswas = $dosen->mahasiswas()->with(['instansi','progressBabs'])->get();

        $stats = [
            'total'   => $mahasiswas->count(),
            'proses'  => $mahasiswas->where('status','proses')->count(),
            'seminar' => $mahasiswas->where('status','seminar')->count(),
            'selesai' => $mahasiswas->where('status','selesai')->count(),
        ];

        // Antrian verifikasi — laporan BAB yang masih menunggu approve dosen
        $pendingVerifikasi = ProgressBab::whereIn('mahasiswa_id', $mahasiswas->pluck('id'))
            ->where('verifikasi_status', 'menunggu')
            ->with('mahasiswa')
            ->orderBy('file_uploaded_at')
            ->get();

        // Seminar terdekat — hanya yang belum lewat, dibatasi 3
        $seminars = Seminar::whereIn('mahasiswa_id', $mahasiswas->pluck('id'))
            ->where('tanggal', '>=', now()->toDateString())
            ->orderBy('tanggal')
            ->orderBy('jam')
            ->with('mahasiswa')
            ->take(3)
            ->get();

        return view('dosen.dashboard', compact('dosen','stats','seminars','pendingVerifikasi'));
    }
}