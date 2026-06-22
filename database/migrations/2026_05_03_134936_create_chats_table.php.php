<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('chats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('instansi_id')->constrained('instansis')->onDelete('cascade');
            $table->foreignId('dosen_id')->constrained('dosens')->onDelete('cascade');
            $table->foreignId('mahasiswa_id')->nullable()->constrained('mahasiswas')->onDelete('set null');
            $table->string('subjek');
            // tipe: 'monitoring' (laporan kegiatan) | 'pengaduan' (masalah mahasiswa) | 'umum'
            $table->enum('tipe', ['monitoring', 'pengaduan', 'umum'])->default('umum');
            $table->enum('status', ['open', 'closed'])->default('open');
            $table->timestamps();
        });

        Schema::create('chat_pesan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('chat_id')->constrained('chats')->onDelete('cascade');
            // pengirim bisa instansi atau dosen
            $table->enum('pengirim_role', ['instansi', 'dosen']);
            $table->foreignId('pengirim_id'); // id dari tabel instansis atau dosens
            $table->text('pesan');
            $table->boolean('dibaca')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chat_pesan');
        Schema::dropIfExists('chats');
    }
};