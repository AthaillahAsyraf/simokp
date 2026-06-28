<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\ProgressBab;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ProgressController extends Controller
{
    public function index()
    {
        $mahasiswa = Auth::user()->mahasiswa->load('progressBabs');
        return view('mahasiswa.progress.index', compact('mahasiswa'));
    }

    /**
     * Mahasiswa upload soft file laporan untuk satu BAB.
     * Hanya boleh kalau BAB sebelumnya sudah approved (sequential).
     */
    public function upload(Request $request, ProgressBab $progressBab)
    {
        $mahasiswa = Auth::user()->mahasiswa;
        abort_if($progressBab->mahasiswa_id !== $mahasiswa->id, 403);

        if (!$progressBab->bisaDiupload()) {
            return back()->with('error', "Selesaikan dan tunggu persetujuan {$progressBab->babSebelumnya()} terlebih dahulu sebelum mengupload {$progressBab->bab}.");
        }

        $validator = Validator::make($request->all(), [
            'file' => 'required|file|mimes:pdf,doc,docx|max:10240',
        ], [
            'file.required' => 'File laporan wajib dipilih.',
            'file.mimes'    => 'File harus berformat PDF, DOC, atau DOCX.',
            'file.max'      => 'Ukuran file maksimal 10MB.',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator, 'upload')->withInput()->with('upload_bab_id', $progressBab->id);
        }

        try {
            DB::transaction(function () use ($request, $progressBab) {
                // Hapus file lama kalau ada (re-upload / revisi)
                if ($progressBab->file) {
                    Storage::disk('public')->delete($progressBab->file);
                }

                $uploaded = $request->file('file');
                $path = $uploaded->store('laporan_bab/'.$progressBab->mahasiswa_id, 'public');

                $progressBab->update([
                    'file'              => $path,
                    'file_asli'         => $uploaded->getClientOriginalName(),
                    'file_uploaded_at'  => now(),
                    'verifikasi_status' => 'menunggu',
                    // status tetap 'belum' sampai dosen approve
                ]);
            });
        } catch (\Throwable $e) {
            return back()->with('db_error', 'Gagal mengupload file laporan. Silakan coba lagi.');
        }

        return back()->with('success', "File {$progressBab->bab} berhasil diupload, menunggu verifikasi dosen pembimbing.");
    }
}