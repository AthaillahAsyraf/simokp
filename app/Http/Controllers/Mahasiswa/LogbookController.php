<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\Logbook;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LogbookController extends Controller
{
    public function index()
    {
        $mahasiswa = Auth::user()->mahasiswa;
        $logbooks  = Logbook::where('mahasiswa_id', $mahasiswa->id)
                        ->latest('tanggal')->paginate(15);

        return view('mahasiswa.logbook.index', compact('mahasiswa', 'logbooks'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'tanggal'    => 'required|date',
            'kegiatan'   => 'required|string|max:1000',
            'jam_mulai'  => 'nullable|date_format:H:i',
            'jam_selesai'=> 'nullable|date_format:H:i|after:jam_mulai',
        ]);

        $mahasiswa = Auth::user()->mahasiswa;

        Logbook::create([
            'mahasiswa_id' => $mahasiswa->id,
            'tanggal'      => $request->tanggal,
            'kegiatan'     => $request->kegiatan,
            'jam_mulai'    => $request->jam_mulai,
            'jam_selesai'  => $request->jam_selesai,
            'status_instansi' => 'pending',
        ]);

        return back()->with('success', 'Logbook berhasil ditambahkan.');
    }

    public function destroy(Logbook $logbook)
    {
        $mahasiswa = Auth::user()->mahasiswa;
        abort_if($logbook->mahasiswa_id !== $mahasiswa->id, 403);
        abort_if($logbook->status_instansi !== 'pending', 403, 'Logbook yang sudah diverifikasi tidak bisa dihapus.');

        $logbook->delete();
        return back()->with('success', 'Logbook dihapus.');
    }
}