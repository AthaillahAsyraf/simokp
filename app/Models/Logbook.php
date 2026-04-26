<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Logbook extends Model
{
    protected $fillable = [
        'mahasiswa_id', 'tanggal', 'kegiatan',
        'jam_mulai', 'jam_selesai', 'status_instansi', 'catatan_instansi'
    ];

    public function mahasiswa() { return $this->belongsTo(Mahasiswa::class); }
}