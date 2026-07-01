<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;


return new class extends Migration {
    public function up(): void {
        Schema::table('surats', function (Blueprint $table) {
            $table->string('pengirim_role')->nullable()->after('mahasiswa_id');
            $table->unsignedBigInteger('pengirim_id')->nullable()->after('pengirim_role');
            $table->string('penerima_role')->nullable()->after('pengirim_id');
            $table->unsignedBigInteger('penerima_id')->nullable()->after('penerima_role');
            $table->foreignId('parent_id')->nullable()->after('penerima_id')
                ->constrained('surats')->nullOnDelete();
            $table->string('perihal')->nullable()->after('parent_id');
            $table->text('catatan')->nullable()->after('catatan_admin');
            $table->timestamp('dibaca_at')->nullable()->after('file');
        });

        // Backfill data lama (kalau ada) supaya tetap konsisten: anggap semua
        // surat lama itu permohonan dari mahasiswa ke admin.
        DB::table('surats')->whereNull('pengirim_role')->update([
            'pengirim_role'  => 'mahasiswa',
            'penerima_role'  => 'admin',
            'perihal'        => 'Permohonan Surat',
            'catatan'        => DB::raw('catatan_admin'),
        ]);
        DB::table('surats')
            ->whereNull('pengirim_id')
            ->update(['pengirim_id' => DB::raw('mahasiswa_id')]);

        Schema::table('surats', function (Blueprint $table) {
            $table->dropColumn('catatan_admin');
        });

        // jenis & status dilonggarkan dari enum ke string biasa, supaya gampang
        // nambah jenis/status baru ke depannya (lihat Surat::JENIS_* & STATUS_*)
        Schema::table('surats', function (Blueprint $table) {
            $table->string('jenis')->default('umum')->change();
            $table->string('status')->default('terkirim')->change();
        });
    }

    public function down(): void {
        Schema::table('surats', function (Blueprint $table) {
            $table->text('catatan_admin')->nullable()->after('file');
        });
        DB::table('surats')->update(['catatan_admin' => DB::raw('catatan')]);

        Schema::table('surats', function (Blueprint $table) {
            $table->dropConstrainedForeignId('parent_id');
            $table->dropColumn([
                'pengirim_role', 'pengirim_id', 'penerima_role', 'penerima_id',
                'perihal', 'catatan', 'dibaca_at',
            ]);
        });
    }
};