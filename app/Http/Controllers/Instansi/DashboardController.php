<?php

namespace App\Http\Controllers\Instansi;

use App\Http\Controllers\Controller;
use App\Models\Mahasiswa;
use App\Models\Logbook;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $instansi   = Auth::user()->instansi;
        $mahasiswas = Mahasiswa::with(['dosen', 'progressBabs'])
                        ->where('instansi_id', $instansi->id)->get();

        $stats = [
            'total'          => $mahasiswas->count(),
            'logbook_pending'=> Logbook::whereIn('mahasiswa_id', $mahasiswas->pluck('id'))
                                    ->where('status_instansi', 'pending')->count(),
            'sudah_dinilai'  => $mahasiswas->filter(fn($m) => $m->nilai && $m->nilai->nilai_instansi)->count(),
        ];

        return view('instansi.dashboard', compact('instansi', 'mahasiswas', 'stats'));
    }
}