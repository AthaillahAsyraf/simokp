<?php

namespace App\Http\Controllers\Instansi;

use App\Http\Controllers\Controller;
use App\Models\Logbook;
use App\Models\Mahasiswa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LogbookController extends Controller
{
    public function index()
    {
        $instansi   = Auth::user()->instansi;
        $mhsIds     = Mahasiswa::where('instansi_id', $instansi->id)->pluck('id');
        $logbooks   = Logbook::with('mahasiswa')
                        ->whereIn('mahasiswa_id', $mhsIds)
                        ->latest('tanggal')->paginate(20);

        return view('instansi.logbook.index', compact('logbooks'));
    }

    public function approve(Logbook $logbook)
    {
        $this->authorize($logbook);
        $logbook->update(['status_instansi' => 'disetujui']);
        return back()->with('success', 'Logbook disetujui.');
    }

    public function reject(Request $request, Logbook $logbook)
    {
        $this->authorize($logbook);
        $request->validate(['catatan_instansi' => 'nullable|string|max:300']);
        $logbook->update([
            'status_instansi'   => 'ditolak',
            'catatan_instansi'  => $request->catatan_instansi,
        ]);
        return back()->with('success', 'Logbook ditolak.');
    }

    private function authorize(Logbook $logbook): void
    {
        $instansi = Auth::user()->instansi;
        $mhsIds   = Mahasiswa::where('instansi_id', $instansi->id)->pluck('id');
        abort_if(!$mhsIds->contains($logbook->mahasiswa_id), 403);
    }
}