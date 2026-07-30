<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Surat balasan yang diunggah sebelum fitur verifikasi ditambahkan dahulu
     * langsung memajukan tahap ke menunggu_instansi. Kembalikan hanya data yang
     * belum mendaftarkan instansi agar dapat diverifikasi admin.
     */
    public function up(): void {
        DB::table('syarat_administrasis')
            ->join('mahasiswas', 'mahasiswas.id', '=', 'syarat_administrasis.mahasiswa_id')
            ->whereNotNull('syarat_administrasis.file_surat_balasan')
            ->whereNull('syarat_administrasis.surat_balasan_status')
            ->whereNull('mahasiswas.instansi_id')
            ->where('mahasiswas.tahap', 'menunggu_instansi')
            ->update([
                'syarat_administrasis.surat_balasan_status' => 'menunggu_verifikasi',
                'mahasiswas.tahap' => 'menunggu_verifikasi_surat_balasan',
            ]);
    }

    public function down(): void {
        // Data yang sudah menunggu verifikasi tidak dikembalikan otomatis.
    }
};
