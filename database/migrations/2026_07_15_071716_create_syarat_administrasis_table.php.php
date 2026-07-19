<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('syarat_administrasis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mahasiswa_id')->unique()->constrained('mahasiswas')->cascadeOnDelete();

            // 4 berkas sesuai Prosedur KP: Form pengajuan, Bukti SPP, KRS, Transkrip Nilai
            $table->string('file_form_pengajuan')->nullable();
            $table->string('file_form_pengajuan_asli')->nullable();

            $table->string('file_bukti_spp')->nullable();
            $table->string('file_bukti_spp_asli')->nullable();

            $table->string('file_krs')->nullable();
            $table->string('file_krs_asli')->nullable();

            $table->string('file_transkrip')->nullable();
            $table->string('file_transkrip_asli')->nullable();

            // belum_lengkap | menunggu_verifikasi | revisi | disetujui
            $table->string('status')->default('belum_lengkap');
            $table->text('catatan')->nullable(); // catatan revisi dari admin
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('diverifikasi_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('syarat_administrasis');
    }
};