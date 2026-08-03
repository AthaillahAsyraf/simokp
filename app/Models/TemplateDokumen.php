<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TemplateDokumen extends Model
{
    public const FORM_PENGAJUAN = 'form_pengajuan';

    protected $fillable = ['kode', 'file', 'nama_asli'];

    public function getFileUrlAttribute(): ?string
    {
        return $this->file ? asset('storage/' . $this->file) : null;
    }
}
