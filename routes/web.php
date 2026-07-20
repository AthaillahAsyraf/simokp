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

    Route::get('mahasiswa',                [App\Http\Controllers\Admin\MahasiswaController::class,'index'])->name('mahasiswa.index');
    Route::post('mahasiswa',               [App\Http\Controllers\Admin\MahasiswaController::class,'store'])->name('mahasiswa.store');
    Route::get('mahasiswa/{mahasiswa}',    [App\Http\Controllers\Admin\MahasiswaController::class,'show'])->name('mahasiswa.show');
    Route::put('mahasiswa/{mahasiswa}',    [App\Http\Controllers\Admin\MahasiswaController::class,'update'])->name('mahasiswa.update');
    Route::delete('mahasiswa/{mahasiswa}', [App\Http\Controllers\Admin\MahasiswaController::class,'destroy'])->name('mahasiswa.destroy');

    Route::get('dosen',             [App\Http\Controllers\Admin\DosenController::class,'index'])->name('dosen.index');
    Route::post('dosen',            [App\Http\Controllers\Admin\DosenController::class,'store'])->name('dosen.store');
    Route::get('dosen/{dosen}',     [App\Http\Controllers\Admin\DosenController::class,'show'])->name('dosen.show');
    Route::put('dosen/{dosen}',     [App\Http\Controllers\Admin\DosenController::class,'update'])->name('dosen.update');
    Route::delete('dosen/{dosen}',  [App\Http\Controllers\Admin\DosenController::class,'destroy'])->name('dosen.destroy');

    Route::get('instansi',                [App\Http\Controllers\Admin\InstansiController::class,'index'])->name('instansi.index');
    Route::post('instansi',               [App\Http\Controllers\Admin\InstansiController::class,'store'])->name('instansi.store');
    Route::get('instansi/{instansi}',     [App\Http\Controllers\Admin\InstansiController::class,'show'])->name('instansi.show');
    Route::put('instansi/{instansi}',     [App\Http\Controllers\Admin\InstansiController::class,'update'])->name('instansi.update');
    Route::delete('instansi/{instansi}',  [App\Http\Controllers\Admin\InstansiController::class,'destroy'])->name('instansi.destroy');
    Route::post('instansi/resolve-lokasi',[App\Http\Controllers\Admin\InstansiController::class,'resolveLokasi'])->name('instansi.resolveLokasi');

    Route::get('persyaratan',                       [App\Http\Controllers\Admin\PersyaratanController::class,'index'])->name('persyaratan.index');
    Route::post('persyaratan/{mahasiswa}/verifikasi',[App\Http\Controllers\Admin\PersyaratanController::class,'verifikasi'])->name('persyaratan.verifikasi');

    Route::get('progress', [App\Http\Controllers\Admin\ProgressController::class,'index'])->name('progress.index');

    Route::get('nilai', [App\Http\Controllers\Admin\NilaiController::class,'index'])->name('nilai.index');

    Route::get('pembimbing',                     [App\Http\Controllers\Admin\PembimbingController::class,'index'])->name('pembimbing.index');
    Route::put('pembimbing-lapangan/{mahasiswa}', [App\Http\Controllers\Admin\PembimbingController::class,'updateLapangan'])->name('pembimbing.updateLapangan');

    Route::get('seminar',                    [App\Http\Controllers\Admin\SeminarController::class,'index'])->name('seminar.index');
    Route::post('seminar',                   [App\Http\Controllers\Admin\SeminarController::class,'store'])->name('seminar.store');
    Route::put('seminar/{seminar}',          [App\Http\Controllers\Admin\SeminarController::class,'update'])->name('seminar.update');
    Route::post('seminar/{seminar}/approve', [App\Http\Controllers\Admin\SeminarController::class,'approve'])->name('seminar.approve');
    Route::post('seminar/{seminar}/reject',  [App\Http\Controllers\Admin\SeminarController::class,'reject'])->name('seminar.reject');
    Route::delete('seminar/{seminar}',       [App\Http\Controllers\Admin\SeminarController::class,'destroy'])->name('seminar.destroy');

    Route::get('absensi',                     [App\Http\Controllers\Admin\AbsensiController::class,'index'])->name('absensi.index');
    Route::get('absensi/{mahasiswa}',         [App\Http\Controllers\Admin\AbsensiController::class,'show'])->name('absensi.show');
    Route::patch('absensi/catatan/{absensi}', [App\Http\Controllers\Admin\AbsensiController::class,'updateCatatan'])->name('absensi.catatan');

    // ── Surat ──
    Route::get('surat',                  [App\Http\Controllers\Admin\SuratController::class,'index'])->name('surat.index');
    Route::post('surat/kirim',           [App\Http\Controllers\Admin\SuratController::class,'kirim'])->name('surat.kirim');
    Route::post('surat/{surat}/approve', [App\Http\Controllers\Admin\SuratController::class,'approve'])->name('surat.approve');
    Route::post('surat/{surat}/reject',  [App\Http\Controllers\Admin\SuratController::class,'reject'])->name('surat.reject');
    Route::post('surat/{surat}/balas',   [App\Http\Controllers\Admin\SuratController::class,'balas'])->name('surat.balas');
});

// ─── DOSEN ───────────────────────────────────────────────────────────────────
Route::prefix('dosen-area')->name('dosen.')->middleware(['auth','role:dosen'])->group(function () {
    Route::get('dashboard', [App\Http\Controllers\Dosen\DashboardController::class,'index'])->name('dashboard');

    Route::get('progress',                           [App\Http\Controllers\Dosen\ProgressController::class,'index'])->name('progress.index');
    Route::post('progress/{progressBab}/verifikasi', [App\Http\Controllers\Dosen\ProgressController::class,'verifikasi'])->name('progress.verifikasi');

    Route::get('seminar', [App\Http\Controllers\Dosen\SeminarController::class,'index'])->name('seminar.index');

    Route::get('nilai',                     [App\Http\Controllers\Dosen\NilaiController::class,'index'])->name('nilai.index');
    Route::put('nilai/{mahasiswa}/seminar', [App\Http\Controllers\Dosen\NilaiController::class,'updateSeminar'])->name('nilai.updateSeminar');
    Route::get('nilai/{mahasiswa}/cetak',   [App\Http\Controllers\Dosen\NilaiController::class,'cetak'])->name('nilai.cetak');

    Route::get  ('chat',              [App\Http\Controllers\Dosen\ChatController::class,'index'])->name('chat.index');
    Route::get  ('chat/{chat}',       [App\Http\Controllers\Dosen\ChatController::class,'show'])->name('chat.show');
    Route::post ('chat/{chat}/reply', [App\Http\Controllers\Dosen\ChatController::class,'reply'])->name('chat.reply');
    Route::patch('chat/{chat}/close', [App\Http\Controllers\Dosen\ChatController::class,'close'])->name('chat.close');

    Route::get('absensi',                     [App\Http\Controllers\Dosen\AbsensiController::class,'index'])->name('absensi.index');
    Route::get('absensi/{mahasiswa}',         [App\Http\Controllers\Dosen\AbsensiController::class,'show'])->name('absensi.show');
    Route::patch('absensi/catatan/{absensi}', [App\Http\Controllers\Dosen\AbsensiController::class,'updateCatatan'])->name('absensi.catatan');

    // ── Surat ──
    Route::get('surat',                [App\Http\Controllers\Dosen\SuratController::class,'index'])->name('surat.index');
    Route::post('surat/kirim',         [App\Http\Controllers\Dosen\SuratController::class,'kirim'])->name('surat.kirim');
    Route::post('surat/{surat}/balas', [App\Http\Controllers\Dosen\SuratController::class,'balas'])->name('surat.balas');
});

// ─── PEMBIMBING LAPANGAN (akun berperan "instansi") ────────────────────────────
Route::prefix('instansi-area')->name('instansi.')->middleware(['auth','role:pembimbing_lapangan'])->group(function () {
    Route::get('dashboard', [App\Http\Controllers\Instansi\DashboardController::class,'index'])->name('dashboard');

    Route::get('nilai',                    [App\Http\Controllers\Instansi\NilaiController::class,'index'])->name('nilai.index');
    Route::put('nilai/{mahasiswa}',        [App\Http\Controllers\Instansi\NilaiController::class,'update'])->name('nilai.update');
    Route::get('nilai/{mahasiswa}/cetak',  [App\Http\Controllers\Instansi\NilaiController::class,'cetak'])->name('nilai.cetak');

    Route::get('surat',                [App\Http\Controllers\Instansi\SuratController::class,'index'])->name('surat.index');
    Route::post('surat/kirim',         [App\Http\Controllers\Instansi\SuratController::class,'kirim'])->name('surat.kirim');
    Route::post('surat/{surat}/balas', [App\Http\Controllers\Instansi\SuratController::class,'balas'])->name('surat.balas');
});

// ─── MAHASISWA ────────────────────────────────────────────────────────────────
Route::prefix('mahasiswa')->name('mahasiswa.')->middleware(['auth','role:mahasiswa'])->group(function () {
    Route::get('dashboard', [App\Http\Controllers\Mahasiswa\DashboardController::class,'index'])->name('dashboard');

    Route::get('profile',      [App\Http\Controllers\Mahasiswa\ProfileController::class,'show'])->name('profile.show');
    Route::get('profile/edit', [App\Http\Controllers\Mahasiswa\ProfileController::class,'edit'])->name('profile.edit');
    Route::put('profile',      [App\Http\Controllers\Mahasiswa\ProfileController::class,'update'])->name('profile.update');

    // ── Persyaratan KP (tahap paling awal, sesuai Prosedur KP) ──
    Route::get('persyaratan',  [App\Http\Controllers\Mahasiswa\PersyaratanController::class,'index'])->name('persyaratan.index');
    Route::post('persyaratan', [App\Http\Controllers\Mahasiswa\PersyaratanController::class,'upload'])->name('persyaratan.upload');

    // ── Surat Balasan Instansi (Prosedur KP poin 7-10). Surat permohonan
    //    dibuat mahasiswa di SAIDATA (di luar SIMOKP); di sini mahasiswa
    //    hanya mengunggah surat balasan dari instansi. ──
    Route::get('surat-balasan',  [App\Http\Controllers\Mahasiswa\SuratBalasanController::class,'index'])->name('surat-balasan.index');
    Route::post('surat-balasan', [App\Http\Controllers\Mahasiswa\SuratBalasanController::class,'upload'])->name('surat-balasan.upload');

    // ── Fitur di bawah ini baru bisa diakses setelah mahasiswa aktif KP
    //    (berkas disetujui admin + instansi & dosen pembimbing sudah ditentukan) ──
    Route::middleware('tahap:aktif_kp')->group(function () {
        Route::get('progress',                        [App\Http\Controllers\Mahasiswa\ProgressController::class,'index'])->name('progress.index');
        Route::post('progress/{progressBab}/upload',  [App\Http\Controllers\Mahasiswa\ProgressController::class,'upload'])->name('progress.upload');

        Route::get('logbook',              [App\Http\Controllers\Mahasiswa\LogbookController::class,'index'])->name('logbook.index');
        Route::post('logbook',             [App\Http\Controllers\Mahasiswa\LogbookController::class,'store'])->name('logbook.store');
        Route::delete('logbook/{logbook}', [App\Http\Controllers\Mahasiswa\LogbookController::class,'destroy'])->name('logbook.destroy');

        Route::get('seminar',  [App\Http\Controllers\Mahasiswa\SeminarController::class,'index'])->name('seminar.index');
        Route::post('seminar', [App\Http\Controllers\Mahasiswa\SeminarController::class,'store'])->name('seminar.store');

        Route::get('nilai',               [App\Http\Controllers\Mahasiswa\NilaiController::class,'index'])->name('nilai.index');
        Route::get('nilai/cetak',         [App\Http\Controllers\Mahasiswa\NilaiController::class,'cetak'])->name('nilai.cetak');
        Route::get('nilai/cetak-lapangan',[App\Http\Controllers\Mahasiswa\NilaiController::class,'cetakLapangan'])->name('nilai.cetakLapangan');

        Route::get('absensi',         [App\Http\Controllers\Mahasiswa\AbsensiController::class,'index'])->name('absensi.index');
        Route::post('absensi/masuk',  [App\Http\Controllers\Mahasiswa\AbsensiController::class,'checkIn'])->name('absensi.checkin');
        Route::post('absensi/pulang', [App\Http\Controllers\Mahasiswa\AbsensiController::class,'checkOut'])->name('absensi.checkout');
    });

    // ── Surat: bisa diakses & kirim surat bebas kapan saja (mis. tanya admin
    //    soal berkas), tapi "minta surat pengantar" (surat.store) baru boleh
    //    setelah berkas disetujui — sudah pasti butuh instansi tujuan. ──
    Route::get('surat', [App\Http\Controllers\Mahasiswa\SuratController::class,'index'])->name('surat.index');
    Route::post('surat/kirim',            [App\Http\Controllers\Mahasiswa\SuratController::class,'kirim'])->name('surat.kirim');
    Route::post('surat/{surat}/balas',    [App\Http\Controllers\Mahasiswa\SuratController::class,'balas'])->name('surat.balas');
    Route::middleware('tahap:menunggu_instansi')->group(function () {
        Route::post('surat',                  [App\Http\Controllers\Mahasiswa\SuratController::class,'store'])->name('surat.store');
        Route::post('surat/{surat}/teruskan', [App\Http\Controllers\Mahasiswa\SuratController::class,'teruskan'])->name('surat.teruskan');

        // ── Daftarkan Instansi & Pembimbing Lapangan (tugas mahasiswa,
        //    dilakukan setelah surat balasan instansi diunggah) ──
        Route::get('instansi',  [App\Http\Controllers\Mahasiswa\InstansiController::class,'index'])->name('instansi.index');
        Route::post('instansi', [App\Http\Controllers\Mahasiswa\InstansiController::class,'store'])->name('instansi.store');
        Route::post('instansi/resolve-lokasi', [App\Http\Controllers\Admin\InstansiController::class,'resolveLokasi'])->name('instansi.resolveLokasi');
    });
});