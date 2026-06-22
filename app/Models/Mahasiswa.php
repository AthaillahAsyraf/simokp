<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Mahasiswa extends Model {
    protected $fillable = [
        'user_id','nim','nama','angkatan','no_hp',
        'dosen_id','instansi_id','tanggal_mulai','tanggal_selesai','status'
    ];

    public function user()         { return $this->belongsTo(User::class); }
    public function dosen()        { return $this->belongsTo(Dosen::class); }
    public function instansi()     { return $this->belongsTo(Instansi::class); }
    public function progressBabs() { return $this->hasMany(ProgressBab::class)->orderBy('id'); }
    public function seminar()      { return $this->hasOne(Seminar::class); }

    /**
     * FIX: Pakai collection yang sudah di-load, bukan query DB baru (hindari N+1)
     */
    public function progressPersen(): int {
        $babs = $this->relationLoaded('progressBabs')
            ? $this->progressBabs
            : $this->progressBabs()->get();

        $total = $babs->count();
        if ($total === 0) return 0;

        $selesai = $babs->where('status', 'selesai')->count();
        return (int) round(($selesai / $total) * 100);
    }

    /**
     * FIX: Pakai collection yang sudah di-load (hindari N+1)
     */
    public function allBabSelesai(): bool {
        $babs = $this->relationLoaded('progressBabs')
            ? $this->progressBabs
            : $this->progressBabs()->get();

        return $babs->where('status', 'belum')->isEmpty();
    }

    /**
     * FIX UTAMA: Tambah 'verifikasi_status' => 'approved'
     * Inilah penyebab ikon tidak terupdate — Blade mensyaratkan approved untuk tampil ✅
     */
    public function selesaikanSampaiUrutan(int $babOrder, ?string $catatan = null): void {
        $babs = ['BAB I','BAB II','BAB III','BAB IV','BAB V'];
        foreach ($babs as $i => $bab) {
            $urutan = $i + 1;
            if ($urutan <= $babOrder) {
                $this->progressBabs()->where('bab', $bab)->update([
                    'status'            => 'selesai',
                    'verifikasi_status' => 'approved', // ← INI YANG KURANG SEBELUMNYA
                    'tanggal_selesai'   => now()->toDateString(),
                    'catatan'           => $urutan === $babOrder ? $catatan : null,
                ]);
            }
        }
        $this->updateStatusOtomatis();
    }

    /**
     * FIX: Reset verifikasi_status ke null saat BAB di-reset
     */
    public function resetDariBab(int $babOrder): void {
        $babs = ['BAB I','BAB II','BAB III','BAB IV','BAB V'];
        foreach ($babs as $i => $bab) {
            if (($i + 1) >= $babOrder) {
                $this->progressBabs()->where('bab', $bab)->update([
                    'status'            => 'belum',
                    'verifikasi_status' => null, // ← RESET JUGA
                    'tanggal_selesai'   => null,
                    'catatan'           => null,
                ]);
            }
        }
        $this->updateStatusOtomatis();
    }

    private function updateStatusOtomatis(): void {
        $this->refresh(); // refresh() otomatis reload relasi yang sudah di-load
        if ($this->allBabSelesai() && $this->status === 'proses') {
            $this->update(['status' => 'seminar']);
        } elseif (!$this->allBabSelesai() && $this->status === 'seminar') {
            $this->update(['status' => 'proses']);
        }
    }
}