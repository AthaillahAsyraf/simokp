<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SuratLampiran extends Model
{
    protected $table = 'surat_lampirans';

    protected $fillable = ['surat_id', 'file', 'nama_asli'];

    public function surat()
    {
        return $this->belongsTo(Surat::class);
    }

    public function getFileUrlAttribute(): string
    {
        return asset('storage/' . $this->file);
    }
}
