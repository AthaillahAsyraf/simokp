<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Seminar extends Model
{
    protected $fillable = [
        'mahasiswa_id', 'tanggal', 'jam', 'ruangan',
        'dosen_penguji', 'status', 'nilai', 'catatan'
    ];

    public function mahasiswa() { return $this->belongsTo(Mahasiswa::class); }
}