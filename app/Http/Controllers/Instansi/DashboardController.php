<?php

namespace App\Http\Controllers\Instansi;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\Nilai;
use App\Models\Logbook; // kalau ada tabel logbook

class DashboardController extends Controller
{
    public function index()
    {
        $instansi   = Auth::user()->instansi;
        $mahasiswas = $instansi->mahasiswas()->with(['dosen', 'bimbingans'])->get();

        // TOTAL MAHASISWA
        $total = $mahasiswas->count();

        // STATUS
        $proses  = $mahasiswas->where('status','proses')->count();
        $seminar = $mahasiswas->where('status','seminar')->count();
        $selesai = $mahasiswas->where('status','selesai')->count();

        // 🔥 LOGBOOK PENDING (AMAN, kalau belum ada fitur = 0)
$logbook_pending = 0;

        // 🔥 SUDAH DINILAI
        $sudah_dinilai = class_exists(Nilai::class)
            ? Nilai::whereIn('mahasiswa_id', $mahasiswas->pluck('id'))
                ->whereNotNull('nilai_akhir')
                ->count()
            : 0;

        $stats = [
            'total'            => $total,
            'proses'           => $proses,
            'seminar'          => $seminar,
            'selesai'          => $selesai,
            'logbook_pending'  => $logbook_pending,
            'sudah_dinilai'    => $sudah_dinilai,
        ];

        return view('instansi.dashboard', compact('instansi','mahasiswas','stats'));
    }
}
