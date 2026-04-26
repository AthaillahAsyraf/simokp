<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

// ============================================================
// AUTH
// ============================================================
Route::middleware('guest')->group(function () {
    Route::get('/login',    [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login',   [AuthController::class, 'login'])->name('login.post');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register',[AuthController::class, 'register'])->name('register.post');
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

Route::get('/', function () {
    if (auth()->check()) {
        return match (auth()->user()->role) {
            'admin' => redirect()->route('admin.dashboard'),
            'dosen' => redirect()->route('dosen.dashboard'),
            'instansi' => redirect()->route('instansi.dashboard'),
            'mahasiswa' => redirect()->route('mahasiswa.dashboard'),
            default => redirect()->route('login'),
        };
    }

    return redirect()->route('login');
});

// ============================================================
// ADMIN
// ============================================================
Route::prefix('admin')->name('admin.')->middleware(['auth', 'role:admin'])->group(function () {

    Route::get('/dashboard', [\App\Http\Controllers\Admin\DashboardController::class, 'index'])
        ->name('dashboard');

    // Mahasiswa
    Route::get('/mahasiswa',             [\App\Http\Controllers\Admin\MahasiswaController::class, 'index'])->name('mahasiswa.index');
    Route::post('/mahasiswa',            [\App\Http\Controllers\Admin\MahasiswaController::class, 'store'])->name('mahasiswa.store');
    Route::get('/mahasiswa/{mahasiswa}', [\App\Http\Controllers\Admin\MahasiswaController::class, 'show'])->name('mahasiswa.show');
    Route::put('/mahasiswa/{mahasiswa}', [\App\Http\Controllers\Admin\MahasiswaController::class, 'update'])->name('mahasiswa.update');
    Route::delete('/mahasiswa/{mahasiswa}', [\App\Http\Controllers\Admin\MahasiswaController::class, 'destroy'])->name('mahasiswa.destroy');

    // Dosen
    Route::get('/dosen',          [\App\Http\Controllers\Admin\DosenController::class, 'index'])->name('dosen.index');
    Route::post('/dosen',         [\App\Http\Controllers\Admin\DosenController::class, 'store'])->name('dosen.store');
    Route::put('/dosen/{dosen}',  [\App\Http\Controllers\Admin\DosenController::class, 'update'])->name('dosen.update');
    Route::delete('/dosen/{dosen}', [\App\Http\Controllers\Admin\DosenController::class, 'destroy'])->name('dosen.destroy');

    // Instansi
    Route::get('/instansi',              [\App\Http\Controllers\Admin\InstansiController::class, 'index'])->name('instansi.index');
    Route::post('/instansi',             [\App\Http\Controllers\Admin\InstansiController::class, 'store'])->name('instansi.store');
    Route::put('/instansi/{instansi}',   [\App\Http\Controllers\Admin\InstansiController::class, 'update'])->name('instansi.update');
    Route::delete('/instansi/{instansi}',[\App\Http\Controllers\Admin\InstansiController::class, 'destroy'])->name('instansi.destroy');

    // Progress BAB
    Route::get('/progress',                [\App\Http\Controllers\Admin\ProgressController::class, 'index'])->name('progress.index');
    Route::put('/progress/{progressBab}',  [\App\Http\Controllers\Admin\ProgressController::class, 'update'])->name('progress.update');

    // Seminar
    Route::get('/seminar',             [\App\Http\Controllers\Admin\SeminarController::class, 'index'])->name('seminar.index');
    Route::post('/seminar',            [\App\Http\Controllers\Admin\SeminarController::class, 'store'])->name('seminar.store');
    Route::put('/seminar/{seminar}',   [\App\Http\Controllers\Admin\SeminarController::class, 'update'])->name('seminar.update');

    // Surat
    Route::get('/surat',                        [\App\Http\Controllers\Admin\SuratController::class, 'index'])->name('surat.index');
    Route::post('/surat/{surat}/approve',       [\App\Http\Controllers\Admin\SuratController::class, 'approve'])->name('surat.approve');
    Route::post('/surat/{surat}/reject',        [\App\Http\Controllers\Admin\SuratController::class, 'reject'])->name('surat.reject');

    // Laporan
    Route::get('/laporan', [\App\Http\Controllers\Admin\LaporanController::class, 'index'])->name('laporan.index');
});

// ============================================================
// DOSEN
// ============================================================
Route::prefix('dosen')->name('dosen.')->middleware(['auth', 'role:dosen'])->group(function () {

    Route::get('/dashboard', [\App\Http\Controllers\Dosen\DashboardController::class, 'index'])
        ->name('dashboard');

    Route::get('/progress',               [\App\Http\Controllers\Dosen\ProgressController::class, 'index'])->name('progress.index');
    Route::put('/progress/{progressBab}', [\App\Http\Controllers\Dosen\ProgressController::class, 'update'])->name('progress.update');

    Route::get('/logbook', [\App\Http\Controllers\Dosen\LogbookController::class, 'index'])->name('logbook.index');

    Route::get('/nilai',                      [\App\Http\Controllers\Dosen\NilaiController::class, 'index'])->name('nilai.index');
    Route::put('/nilai/{mahasiswa}',          [\App\Http\Controllers\Dosen\NilaiController::class, 'update'])->name('nilai.update');
    Route::put('/nilai/{mahasiswa}/seminar',  [\App\Http\Controllers\Dosen\NilaiController::class, 'updateSeminar'])->name('nilai.seminar');
});

// ============================================================
// INSTANSI
// ============================================================
Route::prefix('instansi')->name('instansi.')->middleware(['auth', 'role:instansi'])->group(function () {

    Route::get('/dashboard', [\App\Http\Controllers\Instansi\DashboardController::class, 'index'])
        ->name('dashboard');

    Route::get('/logbook',                    [\App\Http\Controllers\Instansi\LogbookController::class, 'index'])->name('logbook.index');
    Route::post('/logbook/{logbook}/approve', [\App\Http\Controllers\Instansi\LogbookController::class, 'approve'])->name('logbook.approve');
    Route::post('/logbook/{logbook}/reject',  [\App\Http\Controllers\Instansi\LogbookController::class, 'reject'])->name('logbook.reject');

    Route::get('/nilai',              [\App\Http\Controllers\Instansi\NilaiController::class, 'index'])->name('nilai.index');
    Route::put('/nilai/{mahasiswa}',  [\App\Http\Controllers\Instansi\NilaiController::class, 'update'])->name('nilai.update');
});

// ============================================================
// MAHASISWA
// ============================================================
Route::prefix('mahasiswa')->name('mahasiswa.')->middleware(['auth', 'role:mahasiswa'])->group(function () {

    Route::get('/dashboard', [\App\Http\Controllers\Mahasiswa\DashboardController::class, 'index'])
        ->name('dashboard');

    Route::get('/progress',               [\App\Http\Controllers\Mahasiswa\ProgressController::class, 'index'])->name('progress.index');
    Route::put('/progress/{progressBab}', [\App\Http\Controllers\Mahasiswa\ProgressController::class, 'update'])->name('progress.update');

    Route::get('/logbook',           [\App\Http\Controllers\Mahasiswa\LogbookController::class, 'index'])->name('logbook.index');
    Route::post('/logbook',          [\App\Http\Controllers\Mahasiswa\LogbookController::class, 'store'])->name('logbook.store');
    Route::delete('/logbook/{logbook}', [\App\Http\Controllers\Mahasiswa\LogbookController::class, 'destroy'])->name('logbook.destroy');

    Route::get('/seminar', [\App\Http\Controllers\Mahasiswa\SeminarController::class, 'index'])->name('seminar.index');

    Route::get('/surat',    [\App\Http\Controllers\Mahasiswa\SuratController::class, 'index'])->name('surat.index');
    Route::post('/surat',   [\App\Http\Controllers\Mahasiswa\SuratController::class, 'store'])->name('surat.store');
});