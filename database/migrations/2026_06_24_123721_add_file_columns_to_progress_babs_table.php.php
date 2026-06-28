<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tambahan buat fitur upload soft file laporan per BAB.
 * verifikasi_status (kolom lama) dipakai ulang dengan makna baru:
 *   null      -> belum diupload sama sekali
 *   menunggu  -> sudah diupload, nunggu direview dosen
 *   revisi    -> dosen minta revisi (lihat kolom catatan)
 *   approved  -> dosen setujui (otomatis bikin status='selesai')
 */
return new class extends Migration {
    public function up(): void {
        Schema::table('progress_babs', function (Blueprint $table) {
            if (!Schema::hasColumn('progress_babs', 'file')) {
                $table->string('file')->nullable()->after('status');
            }
            if (!Schema::hasColumn('progress_babs', 'file_asli')) {
                $table->string('file_asli')->nullable()->after('file')
                    ->comment('Nama file asli saat diupload, buat ditampilkan ke user');
            }
            if (!Schema::hasColumn('progress_babs', 'file_uploaded_at')) {
                $table->timestamp('file_uploaded_at')->nullable()->after('file_asli');
            }
        });
    }

    public function down(): void {
        Schema::table('progress_babs', function (Blueprint $table) {
            $table->dropColumn(['file', 'file_asli', 'file_uploaded_at']);
        });
    }
};