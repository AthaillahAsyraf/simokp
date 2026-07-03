<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Nilai extends Model
{
    protected $fillable = [
        'mahasiswa_id',
        'nilai_lapangan', 'catatan_lapangan',
        'nilai_seminar', 'nilai_akhir',
        'seminar_penguasaan_materi', 'seminar_sikap_ilmiah', 'seminar_teknik_penyajian',
        'seminar_originalitas', 'seminar_relevansi', 'seminar_penulisan',
        'lapangan_kehadiran', 'lapangan_tata_tertib',
        'lapangan_kerjasama_anggota', 'lapangan_kerjasama_kelompok_lain', 'lapangan_kerjasama_pembimbing',
        'lapangan_inovasi', 'lapangan_tugas', 'lapangan_keseriusan',
    ];

    /**
     * Bobot per aspek "Lembar Penilaian Seminar Kerja Praktik — Dosen
     * Pembimbing" (format resmi jurusan). Total bobot = 100%.
     */
    const BOBOT_SEMINAR = [
        'seminar_penguasaan_materi' => 0.20,
        'seminar_sikap_ilmiah'      => 0.10,
        'seminar_teknik_penyajian'  => 0.10,
        'seminar_originalitas'      => 0.30,
        'seminar_relevansi'         => 0.15,
        'seminar_penulisan'         => 0.15,
    ];

    /**
     * 8 komponen "FORM NILAI PEMBIMBING LAPANGAN" (format resmi instansi),
     * bobotnya SAMA RATA — beda dengan rubrik seminar di atas yang
     * persentasenya berbeda-beda per aspek.
     */
    const KOMPONEN_LAPANGAN = [
        'lapangan_kehadiran', 'lapangan_tata_tertib',
        'lapangan_kerjasama_anggota', 'lapangan_kerjasama_kelompok_lain', 'lapangan_kerjasama_pembimbing',
        'lapangan_inovasi', 'lapangan_tugas', 'lapangan_keseriusan',
    ];

    /**
     * Asumsi nilai batas lulus & skala huruf — SESUAIKAN kalau aturan
     * institusi kamu beda. Banyak kampus pakai >=60 (C) sebagai batas lulus KP.
     */
    const BATAS_LULUS = 60;

    /**
     * CATATAN PENAMAAN: kolom `nilai_seminar` (+ 6 kolom `seminar_*`) di
     * database TETAP memakai nama lama demi menghindari migrasi rename yang
     * berisiko terhadap data yang sudah ada. Tapi di seluruh tampilan (UI)
     * nilai ini sekarang disebut "Nilai Pembimbing" — sejak "Nilai
     * Pembimbing" versi lama (field tunggal, sudah dihapus) digabung ke sini
     * karena substansinya sama: penilaian dari dosen pembimbing.
     */
    public function mahasiswa() { return $this->belongsTo(Mahasiswa::class); }

    // Hitung nilai akhir: 40% pembimbing lapangan + 60% dosen pembimbing (nilai_seminar)
    public function hitungNilaiAkhir(): ?float
    {
        if (!$this->nilai_lapangan || !$this->nilai_seminar) return null;
        return round(
            ($this->nilai_lapangan * 0.4) +
            ($this->nilai_seminar * 0.6), 2
        );
    }

    // Hitung nilai_seminar dari 6 aspek berbobot (lihat BOBOT_SEMINAR). Null kalau
    // ada aspek yang belum diisi — supaya tidak mengira sudah dinilai padahal belum.
    public function hitungNilaiSeminar(): ?float
    {
        foreach (self::BOBOT_SEMINAR as $field => $bobot) {
            if ($this->$field === null) return null;
        }
        $total = 0;
        foreach (self::BOBOT_SEMINAR as $field => $bobot) {
            $total += $this->$field * $bobot;
        }
        return round($total, 2);
    }

    // Hitung nilai_lapangan dari rata-rata sederhana 8 komponen (lihat
    // KOMPONEN_LAPANGAN) — beda dengan nilai_seminar yang berbobot berbeda tiap
    // aspek. Null kalau ada komponen yang belum diisi.
    public function hitungNilaiLapangan(): ?float
    {
        foreach (self::KOMPONEN_LAPANGAN as $field) {
            if ($this->$field === null) return null;
        }
        $total = 0;
        foreach (self::KOMPONEN_LAPANGAN as $field) {
            $total += $this->$field;
        }
        return round($total / count(self::KOMPONEN_LAPANGAN), 2);
    }

    /**
     * Huruf mutu khusus lembar seminar, sesuai tabel konversi di format resmi
     * (beda dengan predikat A/B/C/D/E untuk nilai_akhir keseluruhan di bawah).
     * Dua pita terbawah sama-sama berlabel "BL" (Belum Lulus) — memang begitu
     * di dokumen sumbernya, bukan salah ketik.
     */
    public function getHurufMutuSeminarAttribute(): ?string
    {
        if ($this->nilai_seminar === null) return null;
        return match (true) {
            $this->nilai_seminar >= 76 => 'A',
            $this->nilai_seminar >= 71 => 'B+',
            $this->nilai_seminar >= 66 => 'B',
            $this->nilai_seminar >= 61 => 'C+',
            $this->nilai_seminar >= 56 => 'C',
            default                    => 'BL',
        };
    }

    public function getKomponenLengkapAttribute(): bool
    {
        return $this->nilai_lapangan !== null
            && $this->nilai_seminar !== null;
    }

    public function getPredikatAttribute(): ?string
    {
        if ($this->nilai_akhir === null) return null;
        return match (true) {
            $this->nilai_akhir >= 80 => 'A',
            $this->nilai_akhir >= 70 => 'B',
            $this->nilai_akhir >= 60 => 'C',
            $this->nilai_akhir >= 50 => 'D',
            default                  => 'E',
        };
    }

    public function getStatusKelulusanAttribute(): string
    {
        if (!$this->komponenLengkap) return 'Belum Lengkap';
        return $this->nilai_akhir >= self::BATAS_LULUS ? 'Lulus' : 'Tidak Lulus';
    }
}