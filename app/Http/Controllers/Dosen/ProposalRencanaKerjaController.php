<?php

namespace App\Http\Controllers\Dosen;

use App\Http\Controllers\Controller;
use App\Models\ProposalRencanaKerja;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProposalRencanaKerjaController extends Controller
{
    public function index()
    {
        $dosen = Auth::user()->dosen;
        $proposals = ProposalRencanaKerja::with('mahasiswa.instansi')
            ->whereHas('mahasiswa', fn ($query) => $query->where('dosen_id', $dosen->id))
            ->latest('uploaded_at')->get();

        return view('dosen.proposal-rencana-kerja.index', compact('proposals'));
    }

    public function verifikasi(Request $request, ProposalRencanaKerja $proposal)
    {
        abort_if($proposal->mahasiswa->dosen_id !== Auth::user()->dosen->id, 403);
        $request->validate(['keputusan' => 'required|in:disetujui,revisi', 'catatan' => 'nullable|string|max:1000']);

        if ($proposal->status !== 'menunggu') {
            return back()->with('error', 'Proposal ini sudah diverifikasi.');
        }
        if ($request->keputusan === 'revisi' && !$request->filled('catatan')) {
            return back()->with('error', 'Catatan revisi wajib diisi.');
        }

        $proposal->update([
            'status' => $request->keputusan,
            'catatan' => $request->keputusan === 'revisi' ? $request->catatan : null,
            'diverifikasi_at' => now(),
        ]);

        return back()->with('success', $request->keputusan === 'disetujui' ? 'Proposal disetujui.' : 'Proposal dikembalikan untuk direvisi.');
    }
}
