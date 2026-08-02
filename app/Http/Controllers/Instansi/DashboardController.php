<?php

namespace App\Http\Controllers\Instansi;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\Nilai;
use App\Models\Surat;

class DashboardController extends Controller
{
    public function index()
    {
        $instansi   = Auth::user()->instansi;
        $mahasiswas = $instansi->mahasiswas()->with(['dosen', 'bimbingans', 'nilai'])->get();

        // TOTAL MAHASISWA
        $total = $mahasiswas->count();

        // STATUS
        $proses  = $mahasiswas->where('status','proses')->count();
        $seminar = $mahasiswas->where('status','seminar')->count();
        $selesai = $mahasiswas->where('status','selesai')->count();

        // 🔥 SUDAH DINILAI
        $sudah_dinilai = class_exists(Nilai::class)
            ? Nilai::whereIn('mahasiswa_id', $mahasiswas->pluck('id'))
                ->whereNotNull('nilai_akhir')
                ->count()
            : 0;

        $suratBelumDibaca = Surat::where('penerima_role', 'instansi')
            ->where('penerima_id', $instansi->id)
            ->whereNull('dibaca_at')
            ->count();

        $stats = [
            'total'            => $total,
            'proses'           => $proses,
            'seminar'          => $seminar,
            'selesai'          => $selesai,
            'sudah_dinilai'    => $sudah_dinilai,
            'surat_belum_dibaca' => $suratBelumDibaca,
        ];

        return view('instansi.dashboard', compact('instansi','mahasiswas','stats'));
    }
}