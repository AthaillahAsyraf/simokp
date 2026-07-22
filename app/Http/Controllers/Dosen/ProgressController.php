<?php

namespace App\Http\Controllers\Dosen;

use App\Http\Controllers\Controller;
use App\Models\Bimbingan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProgressController extends Controller
{
    public function index()
    {
        $dosen = Auth::user()->dosen;
        $mahasiswas = $dosen->mahasiswas()->with(['bimbingans', 'instansi'])->get();
        return view('dosen.progress.index', compact('mahasiswas'));
    }

    public function verifikasi(Request $request, Bimbingan $bimbingan)
    {
        $dosen = Auth::user()->dosen;
        abort_if($bimbingan->mahasiswa->dosen_id !== $dosen->id, 403);
        $request->validate(['keputusan' => 'required|in:approved,revisi', 'catatan' => 'nullable|string|max:1000']);
        if ($request->keputusan === 'revisi' && !$request->filled('catatan')) {
            return back()->with('error', 'Catatan revisi wajib diisi supaya mahasiswa tahu tindak lanjutnya.');
        }

        $disetujui = $request->keputusan === 'approved';
        $bimbingan->update([
            'status' => $disetujui ? Bimbingan::STATUS_DISETUJUI : Bimbingan::STATUS_REVISI,
            'catatan_dosen' => $request->catatan,
            'ditinjau_at' => now(),
        ]);
        if ($disetujui && $bimbingan->isPermintaanAccSeminar()) {
            $bimbingan->mahasiswa->update(['status' => 'seminar']);
        }

        $msg = $bimbingan->isPermintaanAccSeminar()
            ? ($disetujui ? 'ACC seminar disetujui. Mahasiswa sudah dapat mendaftar seminar.' : 'Permintaan ACC seminar dikembalikan untuk ditindaklanjuti.')
            : ($disetujui ? 'Unggahan bimbingan disetujui.' : 'Unggahan bimbingan dikembalikan untuk direvisi.');
        return back()->with('success', $msg);
    }
}
