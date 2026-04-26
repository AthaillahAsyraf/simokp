<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class SeminarController extends Controller
{
    public function index()
    {
        $mahasiswa = Auth::user()->mahasiswa->load(['seminar', 'nilai', 'progressBabs', 'surats']);
        return view('mahasiswa.seminar.index', compact('mahasiswa'));
    }
}