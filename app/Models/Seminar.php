<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Seminar extends Model {
    protected $fillable = [
        'mahasiswa_id','judul_kp','tanggal','jam_mulai','jam_selesai','ruangan',
        'dosen_penguji_id','status','diajukan_oleh','catatan',
    ];

    const STATUS_MENUNGGU  = 'menunggu_persetujuan';
    const STATUS_MENUNGGU_ACC_DOSPEM = 'menunggu_acc_dospem';
    const STATUS_ACC_DOSPEM = 'acc_dospem';
    const STATUS_ACC_DITOLAK = 'acc_ditolak_dospem';
    const STATUS_TERJADWAL = 'terjadwal';
    const STATUS_DITOLAK   = 'ditolak';
    const STATUS_SELESAI   = 'selesai';

    public function mahasiswa()    { return $this->belongsTo(Mahasiswa::class); }
    public function dosenPenguji() { return $this->belongsTo(Dosen::class, 'dosen_penguji_id'); }

    public function isPending(): bool   { return $this->status === self::STATUS_MENUNGGU; }
    public function isMenungguAccDospem(): bool { return $this->status === self::STATUS_MENUNGGU_ACC_DOSPEM; }
    public function isAccDospem(): bool { return $this->status === self::STATUS_ACC_DOSPEM; }
    public function isAccDitolakDospem(): bool { return $this->status === self::STATUS_ACC_DITOLAK; }
    public function isTerjadwal(): bool { return $this->status === self::STATUS_TERJADWAL; }
    public function isSelesai(): bool   { return $this->status === self::STATUS_SELESAI; }
    public function isDitolak(): bool   { return $this->status === self::STATUS_DITOLAK; }

    /**
     * Daftar nama ruangan yang pernah dipakai (buat dropdown/datalist).
     * Begitu ada yang ketik ruangan baru & kesimpan, otomatis ikut muncul di sini.
     */
    public static function daftarRuangan() {
        return self::whereNotNull('ruangan')->where('ruangan', '!=', '')
            ->distinct()->orderBy('ruangan')->pluck('ruangan');
    }

    /**
     * Jadwal yang masih aktif (menunggu ATAU terjadwal) ke depan — buat ditampilkan
     * ke mahasiswa sebagai referensi "jadwal yang sudah terisi", tanpa membuka nama
     * mahasiswa lain (privasi).
     */
    public static function jadwalTerisi(?int $kecualiId = null) {
        $q = self::whereIn('status', [
                self::STATUS_MENUNGGU_ACC_DOSPEM,
                self::STATUS_ACC_DOSPEM,
                self::STATUS_MENUNGGU,
                self::STATUS_TERJADWAL,
            ])
            ->where('tanggal', '>=', now()->toDateString())
            ->orderBy('tanggal')->orderBy('jam_mulai');
        if ($kecualiId) $q->where('id', '!=', $kecualiId);
        return $q->get();
    }

    /**
     * Cek bentrok jadwal. Dipisah jadi 2 lapis karena beda tingkat "mengikat":
     *
     * 1) RUANGAN — diklaim sejak diajukan (status menunggu_persetujuan ATAU terjadwal).
     *    Begitu satu mahasiswa pilih ruangan+jam, mahasiswa lain otomatis tidak bisa
     *    ambil slot yang sama lagi, walau belum di-approve admin.
     *
     * 2) DOSEN (penguji & pembimbing) — hanya dicek terhadap yang SUDAH terjadwal,
     *    karena dosen penguji baru ditentukan admin saat approve; pengajuan yang masih
     *    menunggu belum benar-benar mengikat jadwal dosen manapun.
     */
    public static function cekBentrok(
        string $tanggal, string $jamMulai, string $jamSelesai,
        string $ruangan, ?int $dosenPengujiId, ?int $dosenPembimbingId,
        ?int $excludeId = null
    ): ?string {
        // --- 1) Cek ruangan ---
        $statusAktif = [
            self::STATUS_MENUNGGU_ACC_DOSPEM,
            self::STATUS_ACC_DOSPEM,
            self::STATUS_MENUNGGU,
            self::STATUS_TERJADWAL,
        ];

        $queryRuangan = self::with('mahasiswa')
            ->where('tanggal', $tanggal)
            ->whereIn('status', $statusAktif)
            ->where('jam_mulai', '<', $jamSelesai)
            ->where('jam_selesai', '>', $jamMulai)
            ->whereRaw('LOWER(TRIM(ruangan)) = ?', [mb_strtolower(trim($ruangan))]);

        if ($excludeId) $queryRuangan->where('id', '!=', $excludeId);

        $bentrokRuangan = $queryRuangan->first();
        if ($bentrokRuangan) {
            return "Ruangan \"{$ruangan}\" pada {$tanggal} jam {$jamMulai}-{$jamSelesai} sudah dipesan untuk seminar lain.";
        }

        // --- 2) Cek dosen (hanya yang sudah terjadwal pasti) ---
        if ($dosenPengujiId || $dosenPembimbingId) {
            $queryDosen = self::with('mahasiswa')
                ->where('tanggal', $tanggal)
                ->whereIn('status', $statusAktif)
                ->where('jam_mulai', '<', $jamSelesai)
                ->where('jam_selesai', '>', $jamMulai);

            if ($excludeId) $queryDosen->where('id', '!=', $excludeId);

            foreach ($queryDosen->get() as $lain) {
                if ($dosenPengujiId && (int) $lain->dosen_penguji_id === (int) $dosenPengujiId) {
                    return "Dosen penguji yang dipilih sudah punya jadwal seminar lain ({$lain->mahasiswa?->nama}) pada jam tersebut.";
                }
                if ($dosenPembimbingId) {
                    $dosenLainTerlibat = [$lain->dosen_penguji_id, $lain->mahasiswa?->dosen_id];
                    if (in_array($dosenPembimbingId, $dosenLainTerlibat)) {
                        return "Dosen pembimbing mahasiswa ini sudah punya jadwal seminar lain ({$lain->mahasiswa?->nama}) pada jam tersebut.";
                    }
                }
            }
        }

        return null; // aman, tidak bentrok
    }
}
