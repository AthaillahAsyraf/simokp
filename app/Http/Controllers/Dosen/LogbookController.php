<?php

namespace App\Http\Controllers\Dosen;

use App\Http\Controllers\Controller;
use App\Models\Mahasiswa;
use App\Models\Logbook;
use Illuminate\Support\Facades\Auth;

class LogbookController extends Controller
{
    public function index()
    {
        $dosen      = Auth::user()->dosen;
        $mahasiswas = Mahasiswa::where('dosen_id', $dosen->id)->pluck('id');
        $logbooks   = Logbook::with('mahasiswa')
                        ->whereIn('mahasiswa_id', $mahasiswas)
                        ->latest('tanggal')->paginate(15);

        return view('dosen.logbook.index', compact('logbooks'));
    }
}