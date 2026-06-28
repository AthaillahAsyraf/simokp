<?php
namespace App\Http\Controllers\Dosen;
use App\Http\Controllers\Controller;
use App\Models\ProgressBab;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProgressController extends Controller {
    public function index() {
        $dosen      = Auth::user()->dosen;
        $mahasiswas = $dosen->mahasiswas()->with(['progressBabs','instansi'])->get();
        return view('dosen.progress.index', compact('mahasiswas'));
    }

    /**
     * Dosen verifikasi file laporan yang diupload mahasiswa untuk satu BAB.
     * keputusan = approved -> cascade selesai (pakai logic existing di Mahasiswa model)
     * keputusan = revisi   -> BAB tetap belum, mahasiswa wajib upload ulang
     */
    public function verifikasi(Request $request, ProgressBab $progressBab) {
        $dosen = Auth::user()->dosen;
        abort_if($progressBab->mahasiswa->dosen_id !== $dosen->id, 403);

        $request->validate([
            'keputusan' => 'required|in:approved,revisi',
            'catatan'   => 'nullable|string|max:500',
        ], [], ['keputusan' => 'keputusan']);

        if (!$progressBab->file) {
            return back()->with('error', 'Mahasiswa belum mengupload file untuk BAB ini.');
        }

        if ($request->keputusan === 'revisi' && !$request->filled('catatan')) {
            return back()->with('error', 'Catatan revisi wajib diisi supaya mahasiswa tahu apa yang harus diperbaiki.');
        }

        $mahasiswa = $progressBab->mahasiswa;

        if ($request->keputusan === 'approved') {
            // Reuse cascade logic yang sudah teruji di Mahasiswa model
            $mahasiswa->selesaikanSampaiUrutan($progressBab->urutan(), $request->catatan);
            $msg = "{$progressBab->bab} disetujui.";
        } else {
            $progressBab->update([
                'verifikasi_status' => 'revisi',
                'catatan'           => $request->catatan,
            ]);
            $msg = "{$progressBab->bab} dikembalikan untuk direvisi.";
        }

        return back()->with('success', $msg);
    }
}