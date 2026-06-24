<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Absensi extends Model
{
    protected $fillable = [
        'mahasiswa_id', 'tanggal',
        'jam_masuk', 'lat_masuk', 'lng_masuk', 'akurasi_gps_masuk',
        'jarak_masuk', 'foto_masuk', 'status_masuk', 'ip_masuk',
        'jam_keluar', 'lat_keluar', 'lng_keluar', 'akurasi_gps_keluar',
        'jarak_keluar', 'foto_keluar', 'status_keluar', 'ip_keluar',
        'catatan_dosen',
    ];

    protected $casts = [
        'tanggal'    => 'date',
        'lat_masuk'  => 'float',
        'lng_masuk'  => 'float',
        'lat_keluar' => 'float',
        'lng_keluar' => 'float',
    ];

    public function mahasiswa()
    {
        return $this->belongsTo(Mahasiswa::class);
    }

    public function getFotoMasukUrlAttribute(): ?string
    {
        return $this->foto_masuk ? asset('storage/'.$this->foto_masuk) : null;
    }

    public function getFotoKeluarUrlAttribute(): ?string
    {
        return $this->foto_keluar ? asset('storage/'.$this->foto_keluar) : null;
    }

    public function isLengkap(): bool
    {
        return (bool) ($this->jam_masuk && $this->jam_keluar);
    }

    public function perluDitinjau(): bool
    {
        return $this->status_masuk === 'diluar_radius' || $this->status_keluar === 'diluar_radius';
    }
}