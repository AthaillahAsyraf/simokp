<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Nilai extends Model
{
    protected $fillable = [
        'mahasiswa_id',
        'nilai_lapangan', 'catatan_lapangan',
        'nilai_pembimbing', 'catatan_pembimbing',
        'nilai_seminar', 'nilai_akhir'
    ];

    /**
     * Asumsi nilai batas lulus & skala huruf — SESUAIKAN kalau aturan
     * institusi kamu beda. Banyak kampus pakai >=60 (C) sebagai batas lulus KP.
     */
    const BATAS_LULUS = 60;

    public function mahasiswa() { return $this->belongsTo(Mahasiswa::class); }

    // Hitung nilai akhir: 40% pembimbing lapangan + 30% dosen pembimbing + 30% seminar
    public function hitungNilaiAkhir(): ?float
    {
        if (!$this->nilai_lapangan || !$this->nilai_pembimbing || !$this->nilai_seminar) return null;
        return round(
            ($this->nilai_lapangan * 0.4) +
            ($this->nilai_pembimbing * 0.3) +
            ($this->nilai_seminar * 0.3), 2
        );
    }

    public function getKomponenLengkapAttribute(): bool
    {
        return $this->nilai_lapangan !== null
            && $this->nilai_pembimbing !== null
            && $this->nilai_seminar !== null;
    }

    public function getPredikatAttribute(): ?string
    {
        if ($this->nilai_akhir === null) return null;
        return match (true) {
            $this->nilai_akhir >= 80 => 'A',
            $this->nilai_akhir >= 70 => 'B',
            $this->nilai_akhir >= 60 => 'C',
            $this->nilai_akhir >= 50 => 'D',
            default                  => 'E',
        };
    }

    public function getStatusKelulusanAttribute(): string
    {
        if (!$this->komponenLengkap) return 'Belum Lengkap';
        return $this->nilai_akhir >= self::BATAS_LULUS ? 'Lulus' : 'Tidak Lulus';
    }
}