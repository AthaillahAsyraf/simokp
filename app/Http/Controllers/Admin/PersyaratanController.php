<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Mahasiswa;
use App\Models\SyaratAdministrasi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PersyaratanController extends Controller
{
    public function index(Request $request)
    {
        $tahapans = Mahasiswa::LABEL_TAHAP;
        $tahapDipilih = $request->input('tahap');

        // Satu grup per tahap, urut & berlabel persis seperti opsi pada dropdown filter.
        // Dipakai baik untuk tampilan "Semua Tahap KP" maupun saat salah satu tahap difilter,
        // supaya aksi verifikasi / teruskan surat balasan tetap tersedia di kedua mode.
        $mahasiswaPerTahap = collect($tahapans)->mapWithKeys(function ($label, $kodeTahap) use ($request) {
            $data = Mahasiswa::with(['syaratAdministrasi', 'dosen', 'instansi'])
                ->where('tahap', $kodeTahap)
                ->when($request->filled('search'), fn ($q) => $q->where(fn ($q2) => $q2
                    ->where('nama', 'like', '%'.$request->search.'%')
                    ->orWhere('nim', 'like', '%'.$request->search.'%')))
                ->latest('updated_at')
                ->get();

            return [$kodeTahap => $data];
        });

        return view('admin.persyaratan.index', compact('mahasiswaPerTahap', 'tahapans', 'tahapDipilih'))
            ->with('dosens', \App\Models\Dosen::orderBy('nama')->get());
    }

    public function verifikasiSuratBalasan(Request $request, Mahasiswa $mahasiswa)
    {
        $syarat = $mahasiswa->syaratAdministrasi;
        abort_if(!$syarat?->file_surat_balasan || $mahasiswa->tahap !== Mahasiswa::TAHAP_MENUNGGU_VERIFIKASI_SURAT_BALASAN, 422, 'Surat balasan tidak menunggu verifikasi.');
        $data = $request->validate(['keputusan' => 'required|in:disetujui,ditolak', 'catatan' => 'nullable|string|max:1000']);
        $setuju = $data['keputusan'] === 'disetujui';
        $catatan = $data['catatan'] ?? null;
        $syarat->update(['surat_balasan_status' => $setuju ? SyaratAdministrasi::SURAT_BALASAN_DISETUJUI : SyaratAdministrasi::SURAT_BALASAN_REVISI, 'surat_balasan_catatan' => $setuju ? null : ($catatan ?: 'Surat balasan ditolak. Silakan unggah ulang surat yang sesuai.'), 'surat_balasan_diverifikasi_at' => now()]);
        $mahasiswa->update(['tahap' => $setuju ? Mahasiswa::TAHAP_MENUNGGU_INSTANSI : Mahasiswa::TAHAP_UNGGAH_SURAT_BALASAN]);
        return back()->with('success', $setuju ? 'Surat balasan disetujui. Mahasiswa dapat mendaftarkan instansi.' : 'Surat balasan ditolak. Mahasiswa diminta mengunggah ulang.');
    }

    /**
     * Admin menyetujui atau meminta revisi berkas mahasiswa.
     * approved -> tahap mahasiswa maju ke menunggu_instansi
     * revisi   -> tahap mahasiswa kembali ke revisi_berkas, wajib isi catatan
     */
    public function verifikasi(Request $request, Mahasiswa $mahasiswa)
    {
        $syarat = $mahasiswa->syaratAdministrasi;
        abort_if(!$syarat || !$syarat->isLengkap(), 422, 'Berkas mahasiswa ini belum lengkap.');

        $validator = Validator::make($request->all(), [
            'keputusan' => 'required|in:disetujui,revisi',
            'catatan'   => 'nullable|string|max:1000',
        ]);
        if ($validator->fails()) {
            return back()->withErrors($validator, 'verifikasi')->withInput();
        }

        if ($request->keputusan === 'revisi' && !$request->filled('catatan')) {
            return back()->with('error', 'Catatan revisi wajib diisi supaya mahasiswa tahu apa yang harus diperbaiki.');
        }

        if ($request->keputusan === 'disetujui') {
            $syarat->update(['status' => SyaratAdministrasi::STATUS_DISETUJUI, 'catatan' => null, 'diverifikasi_at' => now()]);
            $mahasiswa->update(['tahap' => Mahasiswa::TAHAP_UNGGAH_SURAT_BALASAN]);
            $msg = "Berkas {$mahasiswa->nama} disetujui. Mahasiswa bisa membuat surat permohonan di SAIDATA lalu mengunggah surat balasan instansi.";
        } else {
            $syarat->update(['status' => SyaratAdministrasi::STATUS_REVISI, 'catatan' => $request->catatan, 'diverifikasi_at' => now()]);
            $mahasiswa->update(['tahap' => Mahasiswa::TAHAP_REVISI_BERKAS]);
            $msg = "Berkas {$mahasiswa->nama} dikembalikan untuk direvisi.";
        }

        return back()->with('success', $msg);
    }
}