<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void { Schema::table('seminars', function (Blueprint $table) { $table->date('tanggal')->nullable()->change(); $table->time('jam')->nullable()->change(); $table->string('ruangan')->nullable()->change(); }); }
    public function down(): void { }
};
