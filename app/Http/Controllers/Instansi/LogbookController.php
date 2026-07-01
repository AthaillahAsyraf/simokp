<?php

namespace App\Http\Controllers\Instansi;

use App\Http\Controllers\Controller;
use App\Models\Logbook;
use App\Models\Mahasiswa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LogbookController extends Controller
{
    public function index(Request $request)
    {
        $instansi   = Auth::user()->instansi;
        $mhsIds     = Mahasiswa::where('instansi_id', $instansi->id)->pluck('id');
        $mahasiswas = Mahasiswa::whereIn('id', $mhsIds)->orderBy('nama')->get();

        $query = Logbook::with('mahasiswa')
                    ->whereIn('mahasiswa_id', $mhsIds);

        if ($request->filled('mahasiswa_id')) {
            $query->where('mahasiswa_id', $request->mahasiswa_id);
        }
        if ($request->filled('tanggal_dari')) {
            $query->where('tanggal', '>=', $request->tanggal_dari);
        }
        if ($request->filled('tanggal_sampai')) {
            $query->where('tanggal', '<=', $request->tanggal_sampai);
        }
        if ($request->filled('status')) {
            $query->where('status_instansi', $request->status);
        }

        $logbooks = $query->latest('tanggal')->paginate(20)->withQueryString();

        $baseQuery      = Logbook::whereIn('mahasiswa_id', $mhsIds);
        $totalLogbook   = (clone $baseQuery)->count();
        $totalPending   = (clone $baseQuery)->where('status_instansi', 'pending')->count();
        $totalDisetujui = (clone $baseQuery)->where('status_instansi', 'disetujui')->count();
        $totalDitolak   = (clone $baseQuery)->where('status_instansi', 'ditolak')->count();

        return view('instansi.logbook.index', compact(
            'logbooks', 'instansi', 'mahasiswas',
            'totalLogbook', 'totalPending', 'totalDisetujui', 'totalDitolak'
        ));
    }

    public function approve(Request $request, Logbook $logbook)
    {
        $this->authorize($logbook);
        $logbook->update([
            'status_instansi'  => 'disetujui',
            'catatan_instansi' => $request->catatan ?: null,
        ]);
        return back()->with('success', 'Catatan harian ' . $logbook->mahasiswa->nama . ' berhasil disetujui.');
    }

    public function reject(Request $request, Logbook $logbook)
    {
        $this->authorize($logbook);
        $validated = $request->validateWithBag('reject', [
            'catatan' => 'required|string|max:500',
        ], [
            'catatan.required' => 'Alasan penolakan wajib diisi agar mahasiswa dapat merevisi catatannya.',
        ]);
        $logbook->update([
            'status_instansi'  => 'ditolak',
            'catatan_instansi' => $validated['catatan'],
        ]);
        return back()->with('success', 'Catatan harian ditolak. Mahasiswa akan diminta merevisi.');
    }

    private function authorize(Logbook $logbook): void
    {
        $instansi = Auth::user()->instansi;
        $mhsIds   = Mahasiswa::where('instansi_id', $instansi->id)->pluck('id');
        abort_if(!$mhsIds->contains($logbook->mahasiswa_id), 403);
    }
}