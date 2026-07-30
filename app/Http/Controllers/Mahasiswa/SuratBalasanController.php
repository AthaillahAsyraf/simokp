<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\Mahasiswa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

/**
 * Sesuai Prosedur KP poin 7-10: mahasiswa membuat surat permohonan lewat
 * SAIDATA (di luar SIMOKP) lalu mengirimkannya ke instansi tujuan. Di
 * SIMOKP, mahasiswa hanya perlu mengunggah SURAT BALASAN dari instansi
 * tersebut. File wajib diverifikasi admin sebelum mahasiswa dapat mendaftarkan instansi.
 */
class SuratBalasanController extends Controller
{
    public function index()
    {
        $mahasiswa = Auth::user()->mahasiswa;
        $syarat    = $mahasiswa->syaratAdministrasi;

        if (!$syarat || !$mahasiswa->sudahMencapaiTahap(Mahasiswa::TAHAP_UNGGAH_SURAT_BALASAN)) {
            return redirect()->route('mahasiswa.persyaratan.index')
                ->with('error', 'Surat balasan instansi dapat diunggah setelah persyaratan KP dilengkapi dan disetujui oleh admin.');
        }

        return view('mahasiswa.surat-balasan.index', compact('mahasiswa', 'syarat'));
    }

    public function upload(Request $request)
    {
        $mahasiswa = Auth::user()->mahasiswa;
        $syarat    = $mahasiswa->syaratAdministrasi;

        if (!$syarat || !$mahasiswa->sudahMencapaiTahap(Mahasiswa::TAHAP_UNGGAH_SURAT_BALASAN)) {
            return redirect()->route('mahasiswa.persyaratan.index')
                ->with('error', 'Surat balasan instansi belum dapat diunggah. Lengkapi persyaratan KP terlebih dahulu.');
        }

        if ($mahasiswa->tahap !== Mahasiswa::TAHAP_UNGGAH_SURAT_BALASAN) {
            return back()->with('error', 'Tahap ini sudah selesai atau belum bisa diakses.');
        }

        $validator = Validator::make($request->all(), [
            'file_surat_balasan' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ], [
            'file_surat_balasan.required' => 'Silakan pilih file surat balasan instansi terlebih dahulu.',
            'file_surat_balasan.mimes'    => 'File harus berformat PDF, JPG, atau PNG.',
            'file_surat_balasan.max'      => 'Ukuran file maksimal 5MB.',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator, 'suratBalasan')->withInput();
        }

        if ($syarat->file_surat_balasan) {
            Storage::disk('public')->delete($syarat->file_surat_balasan);
        }

        $uploaded = $request->file('file_surat_balasan');
        $path     = $uploaded->store('surat_balasan/'.$mahasiswa->id, 'public');

        $syarat->update([
            'file_surat_balasan'        => $path,
            'file_surat_balasan_asli'   => $uploaded->getClientOriginalName(),
            'surat_balasan_uploaded_at' => now(),
            'surat_balasan_status'      => \App\Models\SyaratAdministrasi::SURAT_BALASAN_MENUNGGU,
            'surat_balasan_catatan'     => null,
            'surat_balasan_diverifikasi_at' => null,
        ]);

        $mahasiswa->update(['tahap' => Mahasiswa::TAHAP_MENUNGGU_VERIFIKASI_SURAT_BALASAN]);

        return redirect()->route('mahasiswa.dashboard')
            ->with('success', 'Surat balasan berhasil diunggah dan menunggu verifikasi admin jurusan.');
    }
}
