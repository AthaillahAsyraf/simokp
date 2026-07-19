<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Fitur: dosen bisa (opsional) kirim file balik ke mahasiswa saat verifikasi
 * laporan BAB (misal: file koreksi/tanda tangan/lampiran tambahan).
 */
return new class extends Migration {
    public function up(): void {
        Schema::table('progress_babs', function (Blueprint $table) {
            if (!Schema::hasColumn('progress_babs', 'file_dosen')) {
                $table->string('file_dosen')->nullable()->after('catatan');
            }
            if (!Schema::hasColumn('progress_babs', 'file_dosen_asli')) {
                $table->string('file_dosen_asli')->nullable()->after('file_dosen')
                    ->comment('Nama file asli file balasan dosen, buat ditampilkan ke mahasiswa');
            }
            if (!Schema::hasColumn('progress_babs', 'file_dosen_uploaded_at')) {
                $table->timestamp('file_dosen_uploaded_at')->nullable()->after('file_dosen_asli');
            }
        });
    }

    public function down(): void {
        Schema::table('progress_babs', function (Blueprint $table) {
            $table->dropColumn(['file_dosen', 'file_dosen_asli', 'file_dosen_uploaded_at']);
        });
    }
};