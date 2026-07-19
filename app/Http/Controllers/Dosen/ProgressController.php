<?php
namespace App\Http\Controllers\Dosen;
use App\Http\Controllers\Controller;
use App\Models\ProgressBab;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

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
            'keputusan'  => 'required|in:approved,revisi',
            'catatan'    => 'nullable|string|max:500',
            'file_dosen' => 'nullable|file|mimes:pdf,doc,docx,zip|max:10240',
        ], [], ['keputusan' => 'keputusan', 'file_dosen' => 'file lampiran']);

        if (!$progressBab->file) {
            return back()->with('error', 'Mahasiswa belum mengupload file untuk BAB ini.');
        }

        if ($request->keputusan === 'revisi' && !$request->filled('catatan')) {
            return back()->with('error', 'Catatan revisi wajib diisi supaya mahasiswa tahu apa yang harus diperbaiki.');
        }

        $mahasiswa = $progressBab->mahasiswa;

        // Kirim file ke mahasiswa bersifat opsional — dosen boleh melampirkan
        // file koreksi/tanda tangan/lampiran tambahan, atau tidak sama sekali.
        $fileDosenData = [];
        if ($request->hasFile('file_dosen')) {
            if ($progressBab->file_dosen) {
                Storage::disk('public')->delete($progressBab->file_dosen);
            }
            $uploaded = $request->file('file_dosen');
            $path = $uploaded->store('laporan_bab_dosen/'.$mahasiswa->id, 'public');

            $fileDosenData = [
                'file_dosen'             => $path,
                'file_dosen_asli'        => $uploaded->getClientOriginalName(),
                'file_dosen_uploaded_at' => now(),
            ];
        }

        if ($request->keputusan === 'approved') {
            if ($fileDosenData) {
                $progressBab->update($fileDosenData);
            }
            // Reuse cascade logic yang sudah teruji di Mahasiswa model
            $mahasiswa->selesaikanSampaiUrutan($progressBab->urutan(), $request->catatan);
            $msg = "{$progressBab->bab} disetujui.";
        } else {
            $progressBab->update(array_merge([
                'verifikasi_status' => 'revisi',
                'catatan'           => $request->catatan,
            ], $fileDosenData));
            $msg = "{$progressBab->bab} dikembalikan untuk direvisi.";
        }

        if ($fileDosenData) {
            $msg .= ' File dari dosen berhasil dikirim ke mahasiswa.';
        }

        return back()->with('success', $msg);
    }
}