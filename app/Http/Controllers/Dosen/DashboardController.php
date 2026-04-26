<?php

namespace App\Http\Controllers\Dosen;

use App\Http\Controllers\Controller;
use App\Models\Mahasiswa;
use App\Models\Seminar;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $dosen      = Auth::user()->dosen;
        $mahasiswas = Mahasiswa::with(['instansi', 'progressBabs'])
                        ->where('dosen_id', $dosen->id)->get();

        $stats = [
            'total'   => $mahasiswas->count(),
            'proses'  => $mahasiswas->where('status', 'proses')->count(),
            'seminar' => $mahasiswas->where('status', 'seminar')->count(),
            'selesai' => $mahasiswas->where('status', 'selesai')->count(),
        ];

        $seminars = Seminar::whereIn('mahasiswa_id', $mahasiswas->pluck('id'))
                        ->orderBy('tanggal')->get();

        return view('dosen.dashboard', compact('dosen', 'mahasiswas', 'stats', 'seminars'));
    }
}