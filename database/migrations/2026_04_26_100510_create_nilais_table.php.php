<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('nilais', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mahasiswa_id')->constrained('mahasiswas')->cascadeOnDelete();
            $table->decimal('nilai_instansi', 5, 2)->nullable();
            $table->text('catatan_instansi')->nullable();
            $table->decimal('nilai_pembimbing', 5, 2)->nullable();
            $table->text('catatan_pembimbing')->nullable();
            $table->decimal('nilai_seminar', 5, 2)->nullable();
            $table->decimal('nilai_akhir', 5, 2)->nullable(); // auto-calculated
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nilais');
    }
};