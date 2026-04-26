<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Nilai extends Model
{
    protected $fillable = [
        'mahasiswa_id',
        'nilai_instansi', 'catatan_instansi',
        'nilai_pembimbing', 'catatan_pembimbing',
        'nilai_seminar', 'nilai_akhir'
    ];

    public function mahasiswa() { return $this->belongsTo(Mahasiswa::class); }

    // Hitung nilai akhir: 40% instansi + 30% pembimbing + 30% seminar
    public function hitungNilaiAkhir(): ?float
    {
        if (!$this->nilai_instansi || !$this->nilai_pembimbing || !$this->nilai_seminar) return null;
        return round(
            ($this->nilai_instansi * 0.4) +
            ($this->nilai_pembimbing * 0.3) +
            ($this->nilai_seminar * 0.3), 2
        );
    }
}