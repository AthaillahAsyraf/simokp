<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class ProgressBab extends Model {
    protected $fillable = [
        'mahasiswa_id',
        'bab',
        'status',
        'verifikasi_status', // null | menunggu | revisi | approved
        'tanggal_selesai',
        'catatan',
        'file',
        'file_asli',
        'file_uploaded_at',
    ];

    protected $casts = [
        'file_uploaded_at' => 'datetime',
    ];

    public function mahasiswa() { return $this->belongsTo(Mahasiswa::class); }

    /** Nomor urut BAB (1–5) */
    public function urutan(): int {
        $map = ['BAB I'=>1,'BAB II'=>2,'BAB III'=>3,'BAB IV'=>4,'BAB V'=>5];
        return $map[$this->bab] ?? 0;
    }

    public function sudahApproved(): bool {
        return $this->status === 'selesai' && $this->verifikasi_status === 'approved';
    }

    public function getFileUrlAttribute(): ?string {
        return $this->file ? asset('storage/'.$this->file) : null;
    }

    /**
     * BAB cuma boleh diupload kalau BAB sebelumnya sudah approved.
     * BAB I selalu terbuka.
     */
    public function bisaDiupload(): bool {
        if ($this->urutan() <= 1) return true;
        $sebelumnya = $this->mahasiswa->progressBabs->firstWhere('bab', $this->babSebelumnya());
        return $sebelumnya?->sudahApproved() ?? false;
    }

    public function babSebelumnya(): ?string {
        $list = ['BAB I','BAB II','BAB III','BAB IV','BAB V'];
        $idx  = $this->urutan() - 2; // urutan 1-based, array 0-based, mundur 1
        return $idx >= 0 ? $list[$idx] : null;
    }
}