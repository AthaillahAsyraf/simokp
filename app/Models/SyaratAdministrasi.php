<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SyaratAdministrasi extends Model
{
    protected $fillable = [
        'mahasiswa_id',
        'file_form_pengajuan', 'file_form_pengajuan_asli',
        'file_bukti_spp', 'file_bukti_spp_asli',
        'file_krs', 'file_krs_asli',
        'file_transkrip', 'file_transkrip_asli',
        'file_surat_balasan', 'file_surat_balasan_asli', 'surat_balasan_uploaded_at',
        'status', 'catatan', 'submitted_at', 'diverifikasi_at',
    ];

    protected $casts = [
        'submitted_at'              => 'datetime',
        'diverifikasi_at'           => 'datetime',
        'surat_balasan_uploaded_at' => 'datetime',
    ];

    const STATUS_BELUM_LENGKAP     = 'belum_lengkap';
    const STATUS_MENUNGGU_VERIFIKASI = 'menunggu_verifikasi';
    const STATUS_REVISI            = 'revisi';
    const STATUS_DISETUJUI         = 'disetujui';

    /** 4 berkas wajib sesuai Prosedur KP, dipakai untuk cek kelengkapan & render form */
    const BERKAS = [
        'file_form_pengajuan' => 'Form Pengajuan KP',
        'file_bukti_spp'      => 'Bukti Pembayaran SPP',
        'file_krs'            => 'KRS (Kartu Rencana Studi)',
        'file_transkrip'      => 'Transkrip Nilai',
    ];

    public function mahasiswa()
    {
        return $this->belongsTo(Mahasiswa::class);
    }

    public function isLengkap(): bool
    {
        foreach (array_keys(self::BERKAS) as $field) {
            if (!$this->$field) return false;
        }
        return true;
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_MENUNGGU_VERIFIKASI => 'Menunggu Verifikasi',
            self::STATUS_REVISI              => 'Perlu Revisi',
            self::STATUS_DISETUJUI           => 'Disetujui',
            default                           => 'Belum Lengkap',
        };
    }

    public function urlBerkas(string $field): ?string
    {
        return $this->$field ? asset('storage/'.$this->$field) : null;
    }

    public function sudahUploadSuratBalasan(): bool
    {
        return (bool) $this->file_surat_balasan;
    }
}