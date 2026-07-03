<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{Mahasiswa, Instansi};
use Illuminate\Http\Request;

/**
 * Admin cuma monitoring di sini (read-only) — input nilai dilakukan
 * Dosen (nilai_seminar, ditampilkan sebagai "Nilai Pembimbing") dan
 * Pembimbing Lapangan lewat akun Instansi (nilai_lapangan).
 */
class NilaiController extends Controller
{
    public function index(Request $request)
    {
        $query = Mahasiswa::with(['nilai', 'instansi', 'dosen']);

        if ($request->filled('search')) {
            $q = $request->search;
            $query->where(fn ($q2) => $q2->where('nama', 'like', "%$q%")->orWhere('nim', 'like', "%$q%"));
        }
        if ($request->filled('instansi')) {
            $query->where('instansi_id', $request->instansi);
        }
        if ($request->filled('status_kelulusan')) {
            $status = $request->status_kelulusan;
            $query->where(function ($q2) use ($status) {
                if ($status === 'Belum Lengkap') {
                    $q2->whereDoesntHave('nilai')->orWhereHas('nilai', fn ($q3) => $q3
                        ->whereNull('nilai_lapangan')->orWhereNull('nilai_seminar'));
                } elseif ($status === 'Lulus') {
                    $q2->whereHas('nilai', fn ($q3) => $q3->whereNotNull('nilai_akhir')->where('nilai_akhir', '>=', \App\Models\Nilai::BATAS_LULUS));
                } else {
                    $q2->whereHas('nilai', fn ($q3) => $q3->whereNotNull('nilai_akhir')->where('nilai_akhir', '<', \App\Models\Nilai::BATAS_LULUS));
                }
            });
        }

        $mahasiswas = $query->latest()->get();
        $instansis  = Instansi::orderBy('nama')->get();

        return view('admin.nilai.index', compact('mahasiswas', 'instansis'));
    }
}