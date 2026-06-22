<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class ProgressBab extends Model {
    protected $fillable = [
        'mahasiswa_id',
        'bab',
        'status',
        'verifikasi_status', // ← tambahan
        'tanggal_selesai',
        'catatan',
    ];

    public function mahasiswa() { return $this->belongsTo(Mahasiswa::class); }

    /** Nomor urut BAB (1–5) */
    public function urutan(): int {
        $map = ['BAB I'=>1,'BAB II'=>2,'BAB III'=>3,'BAB IV'=>4,'BAB V'=>5];
        return $map[$this->bab] ?? 0;
    }
}