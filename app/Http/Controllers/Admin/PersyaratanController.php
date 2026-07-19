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
        $query = Mahasiswa::with('syaratAdministrasi')
            ->whereIn('tahap', [
                Mahasiswa::TAHAP_LENGKAPI_BERKAS,
                Mahasiswa::TAHAP_MENUNGGU_VERIFIKASI,
                Mahasiswa::TAHAP_REVISI_BERKAS,
            ]);

        if ($request->filled('search')) {
            $q = $request->search;
            $query->where(fn ($q2) => $q2->where('nama', 'like', "%$q%")->orWhere('nim', 'like', "%$q%"));
        }

        $menungguVerifikasi = (clone $query)->where('tahap', Mahasiswa::TAHAP_MENUNGGU_VERIFIKASI)->latest('updated_at')->get();
        $belumLengkapOrRevisi = (clone $query)->whereIn('tahap', [Mahasiswa::TAHAP_LENGKAPI_BERKAS, Mahasiswa::TAHAP_REVISI_BERKAS])->latest('updated_at')->get();
        $sudahDisetujui = Mahasiswa::with('syaratAdministrasi')
            ->whereHas('syaratAdministrasi', fn ($q) => $q->where('status', SyaratAdministrasi::STATUS_DISETUJUI))
            ->latest('updated_at')->get();

        return view('admin.persyaratan.index', compact('menungguVerifikasi', 'belumLengkapOrRevisi', 'sudahDisetujui'));
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