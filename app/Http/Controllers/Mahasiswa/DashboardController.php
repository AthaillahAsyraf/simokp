<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $mahasiswa = Auth::user()->mahasiswa->load([
            'dosen', 'instansi', 'progressBabs',
            'logbooks', 'seminar', 'surats', 'nilai'
        ]);

        return view('mahasiswa.dashboard', compact('mahasiswa'));
    }
}