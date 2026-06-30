<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Mahasiswa extends Model {
    protected $fillable = [
        'user_id','nim','nama','angkatan','no_hp',
        'foto_profil','bio',
        'dosen_id','instansi_id','tanggal_mulai','tanggal_selesai','status',
        'pembimbing_lapangan_nama','pembimbing_lapangan_jabatan','pembimbing_lapangan_no_hp',
    ];

    public function user()         { return $this->belongsTo(User::class); }
    public function dosen()        { return $this->belongsTo(Dosen::class); }
    public function instansi()     { return $this->belongsTo(Instansi::class); }
    public function progressBabs() { return $this->hasMany(ProgressBab::class)->orderBy('id'); }
    public function seminar()      { return $this->hasOne(Seminar::class); }
    public function nilai()        { return $this->hasOne(Nilai::class); }

    // URL foto profil atau null
    public function fotoUrl(): ?string {
        return $this->foto_profil
            ? Storage::url($this->foto_profil)
            : null;
    }

    // Inisial nama untuk avatar fallback (misal "Budi Santoso" → "BS")
    public function inisial(): string {
        $parts = explode(' ', $this->nama);
        $init  = strtoupper(substr($parts[0], 0, 1));
        if (count($parts) > 1) $init .= strtoupper(substr(end($parts), 0, 1));
        return $init;
    }

    public function progressPersen(): int {
        $babs = $this->relationLoaded('progressBabs')
            ? $this->progressBabs
            : $this->progressBabs()->get();

        $total = $babs->count();
        if ($total === 0) return 0;

        $selesai = $babs->where('status', 'selesai')->count();
        return (int) round(($selesai / $total) * 100);
    }

    public function allBabSelesai(): bool {
        $babs = $this->relationLoaded('progressBabs')
            ? $this->progressBabs
            : $this->progressBabs()->get();

        return $babs->where('status', 'belum')->isEmpty();
    }

    public function selesaikanSampaiUrutan(int $babOrder, ?string $catatan = null): void {
        $babs = ['BAB I','BAB II','BAB III','BAB IV','BAB V'];
        foreach ($babs as $i => $bab) {
            $urutan = $i + 1;
            if ($urutan <= $babOrder) {
                $this->progressBabs()->where('bab', $bab)->update([
                    'status'            => 'selesai',
                    'verifikasi_status' => 'approved',
                    'tanggal_selesai'   => now()->toDateString(),
                    'catatan'           => $urutan === $babOrder ? $catatan : null,
                ]);
            }
        }
        $this->updateStatusOtomatis();
    }

    public function resetDariBab(int $babOrder): void {
        $babs = ['BAB I','BAB II','BAB III','BAB IV','BAB V'];
        foreach ($babs as $i => $bab) {
            if (($i + 1) >= $babOrder) {
                $this->progressBabs()->where('bab', $bab)->update([
                    'status'            => 'belum',
                    'verifikasi_status' => null,
                    'tanggal_selesai'   => null,
                    'catatan'           => null,
                ]);
            }
        }
        $this->updateStatusOtomatis();
    }

    private function updateStatusOtomatis(): void {
        $this->refresh();
        if ($this->allBabSelesai() && $this->status === 'proses') {
            $this->update(['status' => 'seminar']);
        } elseif (!$this->allBabSelesai() && $this->status === 'seminar') {
            $this->update(['status' => 'proses']);
        }
    }
}