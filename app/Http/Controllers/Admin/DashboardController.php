<?php
// ─── Admin/DashboardController.php ──────────────────────────────────────────
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\{Mahasiswa, Dosen, Instansi, Seminar};

class DashboardController extends Controller {
    public function index() {
        $stats = [
            'total'    => Mahasiswa::count(),
            'proses'   => Mahasiswa::where('status','proses')->count(),
            'seminar'  => Mahasiswa::where('status','seminar')->count(),
            'selesai'  => Mahasiswa::where('status','selesai')->count(),
            'dosen'    => Dosen::count(),
            'instansi' => Instansi::count(),
            'menunggu_berkas' => Mahasiswa::where('tahap', Mahasiswa::TAHAP_MENUNGGU_VERIFIKASI)->count(),
        ];

        // Mahasiswa yang berkasnya menunggu diverifikasi — actionable buat admin.
        $menungguBerkas = Mahasiswa::with('syaratAdministrasi')
            ->where('tahap', Mahasiswa::TAHAP_MENUNGGU_VERIFIKASI)
            ->latest('updated_at')
            ->take(6)
            ->get();

        // Mahasiswa yang berkasnya sudah disetujui tapi belum ditempatkan
        // instansi & dosen — actionable juga.
        $siapDitempatkan = Mahasiswa::with('syaratAdministrasi')
            ->where('tahap', Mahasiswa::TAHAP_MENUNGGU_INSTANSI)
            ->latest('updated_at')
            ->take(6)
            ->get();

        // Mahasiswa yang sudah siap seminar (ACC seminar disetujui) tapi BELUM
        // punya jadwal seminar sama sekali. Ini gap paling actionable buat
        // admin — progress sudah 100% tapi belum di-book.
        $siapBelumJadwal = Mahasiswa::with(['dosen','instansi'])
            ->where('status', 'seminar')
            ->whereDoesntHave('seminar')
            ->latest('updated_at')
            ->get();

        // Mahasiswa yang masih proses tapi progress-nya paling rendah /
        // paling lama tidak ada update — kandidat "perlu ditindaklanjuti".
        $perluPerhatian = Mahasiswa::with(['bimbingans'])
            ->where('status', 'proses')
            ->get()
            ->sortBy(fn($m) => $m->progressPersen())
            ->take(6)
            ->values();

        // Jadwal seminar yang masih menunggu persetujuan admin — ini butuh
        // aksi (approve/reject/assign ruangan+penguji), bukan sekadar info.
        $seminarMenunggu = Seminar::with('mahasiswa')
            ->where('status', Seminar::STATUS_MENUNGGU)
            ->orderBy('tanggal')
            ->get();

        // Data untuk chart distribusi status (proses/seminar/selesai)
        $statusChart = [
            'labels' => ['Proses', 'Seminar', 'Selesai'],
            'data'   => [$stats['proses'], $stats['seminar'], $stats['selesai']],
        ];

        return view('admin.dashboard', compact(
            'stats', 'siapBelumJadwal', 'perluPerhatian', 'seminarMenunggu', 'statusChart',
            'menungguBerkas', 'siapDitempatkan'
        ));
    }
}
