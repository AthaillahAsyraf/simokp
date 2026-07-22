<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bimbingan extends Model
{
    public const JENIS_LAPORAN = 'laporan';
    public const JENIS_ACC_SEMINAR = 'acc_seminar';
    public const STATUS_MENUNGGU = 'menunggu';
    public const STATUS_REVISI = 'revisi';
    public const STATUS_DISETUJUI = 'disetujui';

    protected $fillable = ['mahasiswa_id', 'jenis', 'keterangan', 'file', 'file_asli', 'status', 'catatan_dosen', 'ditinjau_at'];
    protected $casts = ['ditinjau_at' => 'datetime'];

    public function mahasiswa() { return $this->belongsTo(Mahasiswa::class); }
    public function getFileUrlAttribute(): ?string { return $this->file ? asset('storage/'.$this->file) : null; }
    public function isPermintaanAccSeminar(): bool { return $this->jenis === self::JENIS_ACC_SEMINAR; }
    public function isDisetujui(): bool { return $this->status === self::STATUS_DISETUJUI; }
}
