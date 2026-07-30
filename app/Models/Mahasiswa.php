<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Mahasiswa extends Model {
protected $fillable = [
    'user_id','nim','nama','angkatan','no_hp',
    'dosen_id','instansi_id','tanggal_mulai','tanggal_selesai','status','tahap',
    'pembimbing_lapangan_nama','pembimbing_lapangan_jabatan','pembimbing_lapangan_no_hp',
    'foto_profil','bio',
];

    /**
     * Tahap pra-KP sesuai Prosedur KP (dokumen mutu jurusan), sebelum `status`
     * (proses/seminar/selesai) mulai berlaku. Urutan menaik = makin lanjut.
     */
const TAHAP_LENGKAPI_BERKAS      = 'lengkapi_berkas';
    const TAHAP_MENUNGGU_VERIFIKASI  = 'menunggu_verifikasi';
    const TAHAP_REVISI_BERKAS        = 'revisi_berkas';
    const TAHAP_UNGGAH_SURAT_BALASAN = 'unggah_surat_balasan';
    const TAHAP_MENUNGGU_VERIFIKASI_SURAT_BALASAN = 'menunggu_verifikasi_surat_balasan';
    const TAHAP_MENUNGGU_INSTANSI    = 'menunggu_instansi';
    const TAHAP_AKTIF_KP             = 'aktif_kp';

    const URUTAN_TAHAP = [
        self::TAHAP_LENGKAPI_BERKAS      => 0,
        self::TAHAP_MENUNGGU_VERIFIKASI  => 1,
        self::TAHAP_REVISI_BERKAS        => 1,
        self::TAHAP_UNGGAH_SURAT_BALASAN => 2,
        self::TAHAP_MENUNGGU_VERIFIKASI_SURAT_BALASAN => 2,
        self::TAHAP_MENUNGGU_INSTANSI    => 3,
        self::TAHAP_AKTIF_KP             => 4,
    ];

    const LABEL_TAHAP = [
        self::TAHAP_LENGKAPI_BERKAS      => 'Belum Melengkapi Berkas Persyaratan',
        self::TAHAP_MENUNGGU_VERIFIKASI  => 'Menunggu Verifikasi Admin',
        self::TAHAP_REVISI_BERKAS        => 'Perlu Revisi Berkas',
        self::TAHAP_UNGGAH_SURAT_BALASAN => 'Unggah Surat Balasan Instansi',
        self::TAHAP_MENUNGGU_VERIFIKASI_SURAT_BALASAN => 'Menunggu Verifikasi Surat Balasan',
        self::TAHAP_MENUNGGU_INSTANSI    => 'Menunggu Pemilihan Dosen dan Kirim Akun Instansi',
        self::TAHAP_AKTIF_KP             => 'Aktif Melaksanakan KP',
    ];

    public function user()         { return $this->belongsTo(User::class); }
    public function dosen()        { return $this->belongsTo(Dosen::class); }
    public function instansi()     { return $this->belongsTo(Instansi::class); }
    public function proposalRencanaKerja() { return $this->hasOne(ProposalRencanaKerja::class); }
    public function formKesediaanPembimbing() { return $this->hasOne(FormKesediaanPembimbing::class); }
    public function progressBabs() { return $this->hasMany(ProgressBab::class)->orderBy('id'); }
    public function bimbingans()    { return $this->hasMany(Bimbingan::class)->latest(); }
    public function seminar()      { return $this->hasOne(Seminar::class); }
    public function nilai()        { return $this->hasOne(Nilai::class); }
    public function syaratAdministrasi() { return $this->hasOne(SyaratAdministrasi::class); }

    public function tahapLabel(): string
    {
        return self::LABEL_TAHAP[$this->tahap] ?? $this->tahap;
    }

    /** Sudah mencapai (atau melewati) tahap tertentu? Dipakai middleware `tahap:...` */
    public function sudahMencapaiTahap(string $tahapMinimal): bool
    {
        $urutanSaatIni = self::URUTAN_TAHAP[$this->tahap] ?? 0;
        $urutanMinimal = self::URUTAN_TAHAP[$tahapMinimal] ?? 0;
        return $urutanSaatIni >= $urutanMinimal;
    }

    public function sudahAktifKp(): bool
    {
        return $this->tahap === self::TAHAP_AKTIF_KP;
    }

    /**
     * Dipanggil setelah dosen dan instansi terisi. Admin jurusan menetapkan
     * dosen pembimbing, sehingga form kesediaan langsung diterbitkan sebagai
     * arsip dan mahasiswa dapat memulai KP tanpa persetujuan tambahan dosen.
     */
    public function cekMajukanKeAktifKp(): void
    {
        if ($this->dosen_id && $this->instansi_id
            && $this->tahap !== self::TAHAP_AKTIF_KP
            && $this->sudahMencapaiTahap(self::TAHAP_MENUNGGU_INSTANSI)) {
            $this->formKesediaanPembimbing()->firstOrCreate([], [
                'dosen_id'    => $this->dosen_id,
                'status'      => FormKesediaanPembimbing::STATUS_DITERBITKAN,
                'diterbitkan_at' => now(),
            ]);
            $this->update([
                'tahap'           => self::TAHAP_AKTIF_KP,
                'tanggal_mulai'   => $this->tanggal_mulai ?? now()->toDateString(),
                'tanggal_selesai' => null,
            ]);
        }
    }

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
        $bimbingans = $this->relationLoaded('bimbingans')
            ? $this->bimbingans
            : $this->bimbingans()->get();

        // Progress mengikuti alur bimbingan terbaru, bukan lagi BAB I-V:
        // tiap laporan yang disetujui dosen bernilai 20% (maks. 80%),
        // sedangkan ACC seminar yang disetujui menandakan laporan tuntas.
        if ($bimbingans->contains(fn ($b) =>
            $b->jenis === Bimbingan::JENIS_ACC_SEMINAR && $b->isDisetujui()
        )) {
            return 100;
        }

        $laporanDisetujui = $bimbingans
            ->where('jenis', Bimbingan::JENIS_LAPORAN)
            ->where('status', Bimbingan::STATUS_DISETUJUI)
            ->count();

        return min($laporanDisetujui, 4) * 20;
    }

    public function allBabSelesai(): bool {
        $babs = $this->relationLoaded('progressBabs')
            ? $this->progressBabs
            : $this->progressBabs()->get();

        return $babs->where('status', 'belum')->isEmpty();
    }

    public function seminarSudahDiacc(): bool {
        $bimbingans = $this->relationLoaded('bimbingans') ? $this->bimbingans : $this->bimbingans()->get();
        return $bimbingans->contains(fn ($b) => $b->jenis === Bimbingan::JENIS_ACC_SEMINAR && $b->isDisetujui());
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
