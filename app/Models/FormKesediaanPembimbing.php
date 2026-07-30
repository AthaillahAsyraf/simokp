<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FormKesediaanPembimbing extends Model
{
    public const STATUS_DITERBITKAN = 'diterbitkan';

    protected $fillable = ['mahasiswa_id', 'dosen_id', 'status', 'diterbitkan_at', 'diteruskan_at', 'disetujui_at'];
    protected $casts = ['diterbitkan_at' => 'datetime', 'diteruskan_at' => 'datetime', 'disetujui_at' => 'datetime'];

    public function mahasiswa() { return $this->belongsTo(Mahasiswa::class); }
    public function dosen() { return $this->belongsTo(Dosen::class); }
}
