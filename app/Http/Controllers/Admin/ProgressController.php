<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\{Mahasiswa, ProgressBab, Instansi};
use Illuminate\Http\Request;

/**
 * Admin cuma monitoring di sini (read-only) — verifikasi per BAB sekarang
 * jadi tanggung jawab Dosen Pembimbing (lihat Dosen\ProgressController::verifikasi).
 */
class ProgressController extends Controller {

    public function index(Request $request) {
        $query = Mahasiswa::with(['progressBabs','instansi','dosen']);
        if ($request->filled('search')) {
            $q = $request->search;
            $query->where(fn($q2) => $q2->where('nama','like',"%$q%")->orWhere('nim','like',"%$q%"));
        }
        if ($request->filled('status'))   $query->where('status',$request->status);
        if ($request->filled('instansi')) $query->where('instansi_id',$request->instansi);
        $mahasiswas = $query->latest()->get();
        $instansis  = Instansi::orderBy('nama')->get();
        return view('admin.progress.index', compact('mahasiswas','instansis'));
    }
}