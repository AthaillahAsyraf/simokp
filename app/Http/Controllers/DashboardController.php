<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Mahasiswa;
use App\Models\Dosen;
use App\Models\Instansi;
use App\Models\Seminar;
use App\Models\Surat;
use App\Models\Logbook;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'total_mahasiswa' => Mahasiswa::count(),
            'proses'          => Mahasiswa::where('status', 'proses')->count(),
            'seminar'         => Mahasiswa::where('status', 'seminar')->count(),
            'selesai'         => Mahasiswa::where('status', 'selesai')->count(),
            'total_dosen'     => Dosen::count(),
            'total_instansi'  => Instansi::count(),
            'surat_pending'   => Surat::where('status', 'pending')->count(),
            'logbook_pending' => Logbook::where('status_instansi', 'pending')->count(),
        ];

        $mahasiswas   = Mahasiswa::with(['dosen', 'instansi', 'progressBabs'])->latest()->take(5)->get();
        $seminars     = Seminar::with('mahasiswa')->orderBy('tanggal', 'asc')->take(5)->get();

        return view('admin.dashboard', compact('stats', 'mahasiswas', 'seminars'));
    }
}