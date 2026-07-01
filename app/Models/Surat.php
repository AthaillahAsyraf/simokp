<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Surat extends Model
{
    protected $fillable = [
        'mahasiswa_id',
        'pengirim_role', 'pengirim_id',
        'penerima_role', 'penerima_id',
        'parent_id', 'perihal', 'jenis',
        'keterangan', 'status', 'file', 'catatan', 'dibaca_at',
    ];

    protected $casts = [
        'dibaca_at' => 'datetime',
    ];

    // jenis
    const JENIS_PERMOHONAN = 'permohonan_pengantar'; // mahasiswa -> admin: minta surat pengantar dibuatkan
    const JENIS_PENGANTAR  = 'pengantar';             // admin -> mahasiswa -> instansi: surat pengantar resmi
    const JENIS_BALASAN    = 'balasan';               // instansi/admin -> mahasiswa: balasan
    const JENIS_UMUM       = 'umum';

    // status (makna tergantung jenis — lihat komentar di masing-masing controller)
    const STATUS_PENDING   = 'pending';
    const STATUS_DISETUJUI = 'disetujui';
    const STATUS_DITOLAK   = 'ditolak';
    const STATUS_TERKIRIM  = 'terkirim';

    public function mahasiswa() { return $this->belongsTo(Mahasiswa::class); }
    public function parent()    { return $this->belongsTo(Surat::class, 'parent_id'); }
    public function balasan()   { return $this->hasMany(Surat::class, 'parent_id'); }

    public function getFileUrlAttribute(): ?string {
        return $this->file ? asset('storage/'.$this->file) : null;
    }

    /** Label nama pihak pengirim, lintas role (admin/mahasiswa/instansi) */
    public function getPengirimNamaAttribute(): string {
        return $this->namaUntukRole($this->pengirim_role, $this->pengirim_id);
    }

    /** Label nama pihak penerima, lintas role (admin/mahasiswa/instansi) */
    public function getPenerimaNamaAttribute(): string {
        return $this->namaUntukRole($this->penerima_role, $this->penerima_id);
    }

    private function namaUntukRole(?string $role, ?int $id): string {
        return match ($role) {
            'admin'     => 'Admin Akademik',
            'mahasiswa' => $this->mahasiswa?->nama ?? 'Mahasiswa',
            'instansi'  => Instansi::find($id)?->nama ?? 'Instansi',
            default     => '-',
        };
    }

    public function getJenisLabelAttribute(): string {
        return match ($this->jenis) {
            self::JENIS_PERMOHONAN => 'Permohonan Surat Pengantar',
            self::JENIS_PENGANTAR  => 'Surat Pengantar',
            self::JENIS_BALASAN    => 'Surat Balasan',
            default                => 'Surat',
        };
    }

    /** Surat ini sudah pernah diteruskan ke instansi tertentu? */
    public function sudahDiteruskan(): bool {
        return $this->balasan()->where('jenis', self::JENIS_PENGANTAR)->where('penerima_role', 'instansi')->exists();
    }
}