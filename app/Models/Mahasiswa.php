<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Mahasiswa extends Model
{
    protected $fillable = [
        'user_id', 'nim', 'nama', 'angkatan', 'no_hp',
        'dosen_id', 'instansi_id', 'tanggal_mulai', 'tanggal_selesai', 'status'
    ];

    public function user()       { return $this->belongsTo(User::class); }
    public function dosen()      { return $this->belongsTo(Dosen::class); }
    public function instansi()   { return $this->belongsTo(Instansi::class); }
    public function progressBabs(){ return $this->hasMany(ProgressBab::class); }
    public function logbooks()   { return $this->hasMany(Logbook::class); }
    public function seminar()    { return $this->hasOne(Seminar::class); }
    public function surats()     { return $this->hasMany(Surat::class); }
    public function nilai()      { return $this->hasOne(Nilai::class); }

    // Hitung persentase progress BAB
    public function progressPersen(): int
    {
        $total = $this->progressBabs()->count();
        if ($total === 0) return 0;
        $selesai = $this->progressBabs()->where('status', 'selesai')->count();
        return (int) round(($selesai / $total) * 100);
    }
}