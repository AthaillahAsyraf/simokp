<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class ProposalRencanaKerja extends Model
{
    protected $fillable = [
        'mahasiswa_id', 'file', 'file_asli', 'status', 'catatan', 'uploaded_at', 'diverifikasi_at',
    ];

    protected $casts = [
        'uploaded_at' => 'datetime',
        'diverifikasi_at' => 'datetime',
    ];

    public function mahasiswa()
    {
        return $this->belongsTo(Mahasiswa::class);
    }

    public function getFileUrlAttribute(): string
    {
        return Storage::disk('public')->url($this->file);
    }
}
