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

    // ── Konstanta jenis ──────────────────────────────────────────────────────
    const JENIS_PERMOHONAN = 'permohonan_pengantar'; // mahasiswa → admin: minta surat pengantar
    const JENIS_PENGANTAR  = 'pengantar';             // admin → mahasiswa → instansi: surat pengantar resmi
    const JENIS_BALASAN    = 'balasan';               // balasan dari siapapun
    const JENIS_UMUM       = 'umum';                  // surat bebas antar aktor

    // ── Konstanta status ─────────────────────────────────────────────────────
    const STATUS_PENDING   = 'pending';
    const STATUS_DISETUJUI = 'disetujui';
    const STATUS_DITOLAK   = 'ditolak';
    const STATUS_TERKIRIM  = 'terkirim';

    // ── Relasi ───────────────────────────────────────────────────────────────
    public function mahasiswa() { return $this->belongsTo(Mahasiswa::class); }
    public function parent()    { return $this->belongsTo(Surat::class, 'parent_id'); }
    public function balasan()   { return $this->hasMany(Surat::class, 'parent_id'); }
    public function lampirans() { return $this->hasMany(SuratLampiran::class); }

    /** Semua lampiran surat; tetap menampilkan lampiran lama yang tersimpan di kolom `file`. */
    public function getLampiranListAttribute()
    {
        $lampirans = $this->relationLoaded('lampirans')
            ? $this->lampirans
            : $this->lampirans()->get();

        if ($lampirans->isNotEmpty() || !$this->file) {
            return $lampirans;
        }

        return collect([(object) [
            'file' => $this->file,
            'nama_asli' => basename($this->file),
            'file_url' => $this->file_url,
        ]]);
    }

    // ── Accessor: URL file ───────────────────────────────────────────────────
    public function getFileUrlAttribute(): ?string
    {
        return $this->file ? asset('storage/' . $this->file) : null;
    }

    // ── Accessor: nama pengirim & penerima (lintas semua role) ───────────────
    public function getPengirimNamaAttribute(): string
    {
        return $this->namaUntukRole($this->pengirim_role, $this->pengirim_id);
    }

    public function getPenerimaNamaAttribute(): string
    {
        return $this->namaUntukRole($this->penerima_role, $this->penerima_id);
    }

    private function namaUntukRole(?string $role, ?int $id): string
    {
        return match ($role) {
            'admin'     => 'Admin Akademik',
            'mahasiswa' => Mahasiswa::find($id)?->nama ?? 'Mahasiswa',
            'instansi'  => Instansi::find($id)?->nama  ?? 'Instansi',
            'dosen'     => Dosen::find($id)?->nama      ?? 'Dosen',
            default     => '-',
        };
    }

    // ── Accessor: label jenis ────────────────────────────────────────────────
    public function getJenisLabelAttribute(): string
    {
        return match ($this->jenis) {
            self::JENIS_PERMOHONAN => 'Permohonan Surat Pengantar',
            self::JENIS_PENGANTAR  => 'Surat Pengantar',
            self::JENIS_BALASAN    => 'Surat Balasan',
            default                => 'Surat',
        };
    }

    // ── Helper: cek apakah surat pengantar sudah diteruskan ke instansi ──────
    public function sudahDiteruskan(): bool
    {
        return $this->balasan()
            ->where('jenis', self::JENIS_PENGANTAR)
            ->where('penerima_role', 'instansi')
            ->exists();
    }
}
