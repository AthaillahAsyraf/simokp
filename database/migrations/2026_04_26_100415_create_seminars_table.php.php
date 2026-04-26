<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('seminars', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mahasiswa_id')->constrained('mahasiswas')->cascadeOnDelete();
            $table->date('tanggal');
            $table->time('jam');
            $table->string('ruangan');
            $table->string('dosen_penguji')->nullable();
            $table->enum('status', ['terjadwal', 'hadir', 'tidak_hadir'])->default('terjadwal');
            $table->decimal('nilai', 5, 2)->nullable();
            $table->text('catatan')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seminars');
    }
};