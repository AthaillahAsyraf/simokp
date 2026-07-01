<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Surat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SuratController extends Controller
{
    public function index()
    {
        $permohonanMasuk = Surat::with('mahasiswa')
            ->where('penerima_role', 'admin')
            ->where('jenis', Surat::JENIS_PERMOHONAN)
            ->latest()->get();

        // Semua riwayat surat lintas aktor, buat keperluan monitoring/oversight admin
        $semuaRiwayat = Surat::with('mahasiswa')->latest()->get();

        return view('admin.surat.index', compact('permohonanMasuk', 'semuaRiwayat'));
    }

    /**
     * Admin setujui permohonan & sekaligus upload file surat pengantar resmi.
     * Ini otomatis bikin surat baru (balasan admin -> mahasiswa) berisi file
     * pengantarnya, supaya mahasiswa bisa teruskan ke instansi.
     */
    public function approve(Request $request, Surat $surat)
    {
        abort_unless($surat->jenis === Surat::JENIS_PERMOHONAN && $surat->status === Surat::STATUS_PENDING, 422, 'Permohonan ini sudah diproses.');

        $validator = Validator::make($request->all(), [
            'file' => 'required|file|extensions:pdf,doc,docx|max:10240',
        ], [
            'file.required'    => 'File surat pengantar wajib diupload.',
            'file.extensions'  => 'File harus berformat PDF, DOC, atau DOCX.',
        ]);
        if ($validator->fails()) {
            return back()->withErrors($validator, 'approve')->withInput()->with('approve_id', $surat->id);
        }

        $path = $request->file('file')->store('surat/'.$surat->mahasiswa_id, 'public');

        $surat->update(['status' => Surat::STATUS_DISETUJUI]);

        Surat::create([
            'mahasiswa_id'   => $surat->mahasiswa_id,
            'pengirim_role'  => 'admin',
            'pengirim_id'    => null,
            'penerima_role'  => 'mahasiswa',
            'penerima_id'    => $surat->mahasiswa_id,
            'parent_id'      => $surat->id,
            'perihal'        => $surat->perihal,
            'jenis'          => Surat::JENIS_PENGANTAR,
            'keterangan'     => $request->input('keterangan'),
            'file'           => $path,
            'status'         => Surat::STATUS_TERKIRIM,
        ]);

        return back()->with('success', 'Surat pengantar berhasil dibuat & dikirim ke mahasiswa.');
    }

    public function reject(Request $request, Surat $surat)
    {
        abort_unless($surat->jenis === Surat::JENIS_PERMOHONAN && $surat->status === Surat::STATUS_PENDING, 422, 'Permohonan ini sudah diproses.');

        $request->validate(['catatan' => 'required|string|max:500'], [
            'catatan.required' => 'Alasan penolakan wajib diisi.',
        ]);

        $surat->update(['status' => Surat::STATUS_DITOLAK, 'catatan' => $request->catatan]);

        return back()->with('success', 'Permohonan surat ditolak.');
    }
}