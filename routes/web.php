<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

Route::get('/', function () {
    if (auth()->check()) {
        return redirect(app(\App\Http\Controllers\AuthController::class)
            ->redirectByRole(auth()->user()->role));
    }
    return redirect()->route('login');
});

// AUTH
Route::middleware('guest')->group(function () {
    Route::get('/login',     [AuthController::class,'showLogin'])->name('login');
    Route::post('/login',    [AuthController::class,'login'])->name('login.post');
    Route::get('/register',  [AuthController::class,'showRegister'])->name('register');
    Route::post('/register', [AuthController::class,'register'])->name('register.post');
});
Route::post('/logout', [AuthController::class,'logout'])->name('logout')->middleware('auth');

// Ganti Password (dosen, instansi, mahasiswa)
Route::middleware(['auth'])->group(function () {
    Route::get ('/ganti-password', [AuthController::class,'showGantiPassword'])->name('ganti-password');
    Route::post('/ganti-password', [AuthController::class,'gantiPassword'])->name('ganti-password.post');
});

// ─── ADMIN ───────────────────────────────────────────────────────────────────
Route::prefix('admin')->name('admin.')->middleware(['auth','role:admin'])->group(function () {
    Route::get('dashboard', [\App\Http\Controllers\Admin\DashboardController::class,'index'])->name('dashboard');

    Route::get('mahasiswa',                  [\App\Http\Controllers\Admin\MahasiswaController::class,'index'])->name('mahasiswa.index');
    Route::post('mahasiswa',                 [\App\Http\Controllers\Admin\MahasiswaController::class,'store'])->name('mahasiswa.store');
    Route::get('mahasiswa/{mahasiswa}',      [\App\Http\Controllers\Admin\MahasiswaController::class,'show'])->name('mahasiswa.show');
    Route::put('mahasiswa/{mahasiswa}',      [\App\Http\Controllers\Admin\MahasiswaController::class,'update'])->name('mahasiswa.update');
    Route::delete('mahasiswa/{mahasiswa}',   [\App\Http\Controllers\Admin\MahasiswaController::class,'destroy'])->name('mahasiswa.destroy');

    Route::get('dosen',                [\App\Http\Controllers\Admin\DosenController::class,'index'])->name('dosen.index');
    Route::post('dosen',               [\App\Http\Controllers\Admin\DosenController::class,'store'])->name('dosen.store');
    Route::get('dosen/{dosen}',        [\App\Http\Controllers\Admin\DosenController::class,'show'])->name('dosen.show');
    Route::put('dosen/{dosen}',        [\App\Http\Controllers\Admin\DosenController::class,'update'])->name('dosen.update');
    Route::delete('dosen/{dosen}',     [\App\Http\Controllers\Admin\DosenController::class,'destroy'])->name('dosen.destroy');

    Route::get('instansi',                 [\App\Http\Controllers\Admin\InstansiController::class,'index'])->name('instansi.index');
    Route::post('instansi',                [\App\Http\Controllers\Admin\InstansiController::class,'store'])->name('instansi.store');
    Route::get('instansi/{instansi}',      [\App\Http\Controllers\Admin\InstansiController::class,'show'])->name('instansi.show');
    Route::put('instansi/{instansi}',      [\App\Http\Controllers\Admin\InstansiController::class,'update'])->name('instansi.update');
    Route::delete('instansi/{instansi}',   [\App\Http\Controllers\Admin\InstansiController::class,'destroy'])->name('instansi.destroy');

    Route::get('progress',                [\App\Http\Controllers\Admin\ProgressController::class,'index'])->name('progress.index');
    Route::put('progress/{progressBab}',  [\App\Http\Controllers\Admin\ProgressController::class,'update'])->name('progress.update');

    Route::get('seminar',              [\App\Http\Controllers\Admin\SeminarController::class,'index'])->name('seminar.index');
    Route::post('seminar',             [\App\Http\Controllers\Admin\SeminarController::class,'store'])->name('seminar.store');
    Route::put('seminar/{seminar}',    [\App\Http\Controllers\Admin\SeminarController::class,'update'])->name('seminar.update');
    Route::delete('seminar/{seminar}', [\App\Http\Controllers\Admin\SeminarController::class,'destroy'])->name('seminar.destroy');
});

// ─── DOSEN ───────────────────────────────────────────────────────────────────
// Prefix 'dosen-area' agar tidak bentrok dengan route admin 'dosen/{dosen}'
Route::prefix('dosen-area')->name('dosen.')->middleware(['auth','role:dosen'])->group(function () {
    Route::get('dashboard', [\App\Http\Controllers\Dosen\DashboardController::class,'index'])->name('dashboard');
    Route::get('progress',               [\App\Http\Controllers\Dosen\ProgressController::class,'index'])->name('progress.index');
    Route::put('progress/{progressBab}', [\App\Http\Controllers\Dosen\ProgressController::class,'update'])->name('progress.update');
    Route::post('progress/{progressBab}/verifikasi', [\App\Http\Controllers\Dosen\ProgressController::class,'verifikasi'])->name('progress.verifikasi');
    Route::get('seminar', [\App\Http\Controllers\Dosen\SeminarController::class,'index'])->name('seminar.index');

    // Chat dari Instansi
    Route::get  ('chat',              [\App\Http\Controllers\Dosen\ChatController::class,'index'])->name('chat.index');
    Route::get  ('chat/{chat}',       [\App\Http\Controllers\Dosen\ChatController::class,'show'])->name('chat.show');
    Route::post ('chat/{chat}/reply', [\App\Http\Controllers\Dosen\ChatController::class,'reply'])->name('chat.reply');
    Route::patch('chat/{chat}/close', [\App\Http\Controllers\Dosen\ChatController::class,'close'])->name('chat.close');
});

// ─── INSTANSI ─────────────────────────────────────────────────────────────────
// Prefix 'instansi-area' agar tidak bentrok dengan route admin 'instansi/{instansi}'
Route::prefix('instansi-area')->name('instansi.')->middleware(['auth','role:instansi'])->group(function () {
    Route::get('dashboard',  [\App\Http\Controllers\Instansi\DashboardController::class,'index'])->name('dashboard');
    Route::get('mahasiswa',              [\App\Http\Controllers\Instansi\MahasiswaController::class,'index'])->name('mahasiswa.index');
    Route::get('mahasiswa/{mahasiswa}',  [\App\Http\Controllers\Instansi\MahasiswaController::class,'show'])->name('mahasiswa.show');

    // Chat ke Dosen — 'chat/baru' HARUS sebelum 'chat/{chat}'
    Route::get ('chat',              [\App\Http\Controllers\Instansi\ChatController::class,'index'])->name('chat.index');
    Route::get ('chat/baru',         [\App\Http\Controllers\Instansi\ChatController::class,'create'])->name('chat.create');
    Route::post('chat',              [\App\Http\Controllers\Instansi\ChatController::class,'store'])->name('chat.store');
    Route::get ('chat/{chat}',       [\App\Http\Controllers\Instansi\ChatController::class,'show'])->name('chat.show');
    Route::post('chat/{chat}/reply', [\App\Http\Controllers\Instansi\ChatController::class,'reply'])->name('chat.reply');
});

// ─── MAHASISWA ────────────────────────────────────────────────────────────────
Route::prefix('mahasiswa')->name('mahasiswa.')->middleware(['auth','role:mahasiswa'])->group(function () {
    Route::get('dashboard', [\App\Http\Controllers\Mahasiswa\DashboardController::class,'index'])->name('dashboard');
    Route::get('progress',               [\App\Http\Controllers\Mahasiswa\ProgressController::class,'index'])->name('progress.index');
    Route::put('progress/{progressBab}', [\App\Http\Controllers\Mahasiswa\ProgressController::class,'update'])->name('progress.update');
    Route::get('seminar', [\App\Http\Controllers\Mahasiswa\SeminarController::class,'index'])->name('seminar.index');
});