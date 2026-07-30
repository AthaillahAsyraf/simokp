<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{Dosen, Instansi, Mahasiswa, Seminar};

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'total'    => Mahasiswa::count(),
            'proses'   => Mahasiswa::where('status', 'proses')->count(),
            'seminar'  => Mahasiswa::where('status', 'seminar')->count(),
            'selesai'  => Mahasiswa::where('status', 'selesai')->count(),
            'dosen'    => Dosen::count(),
            'instansi' => Instansi::count(),
        ];

        $jumlahTahap = Mahasiswa::selectRaw('tahap, COUNT(*) as jumlah')
            ->groupBy('tahap')
            ->pluck('jumlah', 'tahap');

        $tahapDistribusi = collect([
            [
                'label' => 'Lengkapi berkas',
                'jumlah' => (int) ($jumlahTahap[Mahasiswa::TAHAP_LENGKAPI_BERKAS] ?? 0),
                'warna' => 'slate',
            ],
            [
                'label' => 'Verifikasi admin',
                'jumlah' => (int) ($jumlahTahap[Mahasiswa::TAHAP_MENUNGGU_VERIFIKASI] ?? 0),
                'warna' => 'amber',
            ],
            [
                'label' => 'Surat balasan',
                'jumlah' => (int) ($jumlahTahap[Mahasiswa::TAHAP_UNGGAH_SURAT_BALASAN] ?? 0),
                'warna' => 'blue',
            ],
            [
                'label' => 'Daftar instansi',
                'jumlah' => (int) ($jumlahTahap[Mahasiswa::TAHAP_MENUNGGU_INSTANSI] ?? 0),
                'warna' => 'purple',
            ],
            [
                'label' => 'Aktif KP',
                'jumlah' => (int) ($jumlahTahap[Mahasiswa::TAHAP_AKTIF_KP] ?? 0),
                'warna' => 'green',
            ],
        ]);

        $menungguBerkas = Mahasiswa::with('syaratAdministrasi')
            ->where('tahap', Mahasiswa::TAHAP_MENUNGGU_VERIFIKASI)
            ->latest('updated_at')
            ->take(4)
            ->get();
        $jumlahMenungguBerkas = (int) ($jumlahTahap[Mahasiswa::TAHAP_MENUNGGU_VERIFIKASI] ?? 0);

        $siapBelumJadwal = Mahasiswa::with(['dosen', 'instansi'])
            ->where('status', 'seminar')
            ->whereDoesntHave('seminar')
            ->latest('updated_at')
            ->take(4)
            ->get();
        $jumlahSiapBelumJadwal = Mahasiswa::where('status', 'seminar')
            ->whereDoesntHave('seminar')
            ->count();

        $seminarMenunggu = Seminar::with('mahasiswa')
            ->where('status', Seminar::STATUS_MENUNGGU)
            ->orderBy('tanggal')
            ->take(4)
            ->get();
        $jumlahSeminarMenunggu = Seminar::where('status', Seminar::STATUS_MENUNGGU)->count();

        $antrianAdmin = collect()
            ->concat($menungguBerkas->map(fn ($m) => [
                'nama' => $m->nama,
                'detail' => "{$m->nim} · Berkas menunggu verifikasi",
                'label' => 'Verifikasi',
                'url' => route('admin.mahasiswa.index', ['tahap' => Mahasiswa::TAHAP_MENUNGGU_VERIFIKASI]),
                'tone' => 'amber',
            ]))
            ->concat($siapBelumJadwal->map(fn ($m) => [
                'nama' => $m->nama,
                'detail' => "{$m->nim} · Siap seminar, belum ada jadwal",
                'label' => 'Jadwalkan',
                'url' => route('admin.seminar.index'),
                'tone' => 'blue',
            ]))
            ->concat($seminarMenunggu->map(fn ($s) => [
                'nama' => $s->mahasiswa?->nama ?? 'Mahasiswa',
                'detail' => 'Pengajuan seminar · ' . \Carbon\Carbon::parse($s->tanggal)->format('d M Y'),
                'label' => 'Tinjau',
                'url' => route('admin.seminar.index', ['status' => Seminar::STATUS_MENUNGGU]),
                'tone' => 'purple',
            ]));

        $seminarMendatang = Seminar::with('mahasiswa')
            ->where('status', Seminar::STATUS_TERJADWAL)
            ->whereDate('tanggal', '>=', today())
            ->orderBy('tanggal')
            ->orderBy('jam_mulai')
            ->take(5)
            ->get();

        $jumlahTindakanAdmin = $jumlahMenungguBerkas + $jumlahSiapBelumJadwal + $jumlahSeminarMenunggu;
        $menungguAksiMahasiswa = (int) ($jumlahTahap[Mahasiswa::TAHAP_UNGGAH_SURAT_BALASAN] ?? 0)
            + (int) ($jumlahTahap[Mahasiswa::TAHAP_MENUNGGU_INSTANSI] ?? 0);

        return view('admin.dashboard', compact(
            'stats',
            'tahapDistribusi',
            'menungguBerkas',
            'siapBelumJadwal',
            'seminarMenunggu',
            'antrianAdmin',
            'seminarMendatang',
            'jumlahMenungguBerkas',
            'jumlahSiapBelumJadwal',
            'jumlahSeminarMenunggu',
            'jumlahTindakanAdmin',
            'menungguAksiMahasiswa',
        ));
    }
}
