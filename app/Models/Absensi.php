<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Absensi extends Model
{
    /** Durasi kerja minimal per hari (jam) sesuai Panduan Kerja Praktik */
    public const DURASI_MINIMAL_JAM = 6;

    protected $fillable = [
        'mahasiswa_id', 'tanggal',
        'jam_masuk', 'lat_masuk', 'lng_masuk', 'akurasi_gps_masuk',
        'jarak_masuk', 'foto_masuk', 'status_masuk', 'ip_masuk',
        'rencana',   // diisi saat absen masuk
        'jam_keluar', 'lat_keluar', 'lng_keluar', 'akurasi_gps_keluar',
        'jarak_keluar', 'foto_keluar', 'status_keluar', 'ip_keluar',
        'realisasi', // diisi saat absen pulang
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
        return $this->status_masuk === 'diluar_radius'
            || $this->status_keluar === 'diluar_radius'
            || $this->isDurasiKurang();
    }

    /** True jika absen sudah lengkap (masuk & pulang) tapi durasi kerja < minimal panduan KP */
    public function isDurasiKurang(): bool
    {
        $durasi = $this->durasi_jam; // accessor getDurasiJamAttribute()
        return $durasi !== null && $durasi < self::DURASI_MINIMAL_JAM;
    }

    /** Daftar alasan (readable) kenapa absensi ini perlu ditinjau — untuk tooltip/detail */
    public function alasanPerluTinjau(): array
    {
        $alasan = [];
        if ($this->status_masuk === 'diluar_radius') {
            $alasan[] = 'Lokasi absen masuk di luar radius instansi';
        }
        if ($this->status_keluar === 'diluar_radius') {
            $alasan[] = 'Lokasi absen pulang di luar radius instansi';
        }
        if ($this->isDurasiKurang()) {
            $alasan[] = 'Durasi kerja '.number_format($this->durasi_jam, 1).' jam, kurang dari minimal '.self::DURASI_MINIMAL_JAM.' jam';
        }
        return $alasan;
    }

    /** Durasi kerja dalam jam (desimal), null jika belum lengkap absen masuk & pulang */
    public function getDurasiJamAttribute(): ?float
    {
        if (!$this->jam_masuk || !$this->jam_keluar) {
            return null;
        }
        $masuk  = Carbon::parse($this->jam_masuk);
        $keluar = Carbon::parse($this->jam_keluar);
        return round($keluar->diffInMinutes($masuk) / 60, 2);
    }
}