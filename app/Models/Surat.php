<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Surat extends Model
{
    protected $fillable = ['mahasiswa_id', 'jenis', 'keterangan', 'status', 'file', 'catatan_admin'];

    public function mahasiswa() { return $this->belongsTo(Mahasiswa::class); }
}