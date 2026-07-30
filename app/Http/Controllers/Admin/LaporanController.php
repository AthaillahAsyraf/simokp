<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Mahasiswa;
use App\Models\Instansi;

class LaporanController extends Controller
{
    public function index()
    {
        $mahasiswas = Mahasiswa::with(['dosen', 'instansi', 'nilai'])->get();
        $instansis  = Instansi::withCount('mahasiswas')->get();

        $stats = [
            'total'    => $mahasiswas->count(),
            'selesai'  => $mahasiswas->where('status', 'selesai')->count(),
            'seminar'  => $mahasiswas->where('status', 'seminar')->count(),
            'proses'   => $mahasiswas->where('status', 'proses')->count(),
        ];

        return view('admin.laporan.index', compact('mahasiswas', 'instansis', 'stats'));
    }
}
