<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\Absensi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class AbsensiController extends Controller
{
    public function index(Request $request)
    {
        $mahasiswa = auth()->user()->mahasiswa;

        if (!$mahasiswa) {
            return redirect()->route('mahasiswa.dashboard')
                ->with('error', 'Data mahasiswa tidak ditemukan untuk akun Anda.');
        }

        $instansi = $mahasiswa->instansi;
        $today    = now()->format('Y-m-d');

        $absensiHariIni = Absensi::where('mahasiswa_id', $mahasiswa->id)
            ->where('tanggal', $today)
            ->first();

        $riwayat = Absensi::where('mahasiswa_id', $mahasiswa->id)
            ->orderByDesc('tanggal')
            ->paginate(10);

        return view('mahasiswa.absensi.index', compact('instansi', 'absensiHariIni', 'riwayat'));
    }

    /** Rekap A4 absensi mahasiswa untuk dicetak atau disimpan sebagai PDF. */
    public function cetak()
    {
        $mahasiswa = auth()->user()->mahasiswa;

        if (!$mahasiswa) {
            return redirect()->route('mahasiswa.dashboard')
                ->with('error', 'Data mahasiswa tidak ditemukan untuk akun Anda.');
        }

        $mahasiswa->load(['dosen', 'instansi']);
        $absensis = Absensi::where('mahasiswa_id', $mahasiswa->id)
            ->whereNotNull('jam_masuk')
            ->orderBy('tanggal')
            ->get();

        return view('mahasiswa.absensi.cetak', compact('mahasiswa', 'absensis'));
    }

    public function checkIn(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'latitude'  => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'accuracy'  => 'required|numeric|min:0',
            'foto'      => 'required|image|mimes:jpeg,jpg,png|max:4096',
            'rencana'   => 'required|string|min:10|max:1000',
            
        ], [
            'foto.required' => 'Foto bukti absen wajib diambil melalui kamera.',
            'latitude.required'  => 'Lokasi GPS tidak terdeteksi. Aktifkan GPS dan izinkan akses lokasi.',
            'longitude.required' => 'Lokasi GPS tidak terdeteksi. Aktifkan GPS dan izinkan akses lokasi.',
            'rencana.required'  => 'Rencana kegiatan hari ini wajib diisi.',
            'rencana.min'       => 'Rencana kegiatan minimal 10 karakter.',  
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator, 'absenMasuk')->withInput();
        }

        $mahasiswa = auth()->user()->mahasiswa;
        $instansi  = $mahasiswa?->instansi;

        if (!$instansi) {
            return back()->with('error', 'Anda belum terdaftar pada instansi KP manapun. Hubungi admin.');
        }

        if (is_null($instansi->latitude) || is_null($instansi->longitude)) {
            return back()->with('error', 'Titik lokasi instansi belum diatur oleh admin, sehingga absen tidak dapat diverifikasi. Hubungi admin untuk mengatur koordinat instansi.');
        }

        $today = now()->format('Y-m-d');

        $existing = Absensi::where('mahasiswa_id', $mahasiswa->id)->where('tanggal', $today)->first();
        if ($existing && $existing->jam_masuk) {
            return back()->with('error', 'Anda sudah melakukan absen masuk hari ini pukul '.
                \Carbon\Carbon::parse($existing->jam_masuk)->format('H:i').'.');
        }

        $jarak  = $this->hitungJarakMeter(
            (float) $request->latitude,
            (float) $request->longitude,
            (float) $instansi->latitude,
            (float) $instansi->longitude
        );
        // Jarak tetap dihitung & dicatat untuk transparansi/audit, tapi TIDAK
        // lagi dipakai untuk menggagalkan absen — status selalu "valid".
        $status = 'valid';

        $path = $request->file('foto')->store('absensi/'.$mahasiswa->id, 'public');

        try {
            DB::transaction(function () use ($mahasiswa, $today, $request, $jarak, $status, $path) {
                Absensi::updateOrCreate(
                    ['mahasiswa_id' => $mahasiswa->id, 'tanggal' => $today],
                    [
                        'jam_masuk'         => now()->format('H:i:s'), // waktu server, bukan waktu device
                        'lat_masuk'         => $request->latitude,
                        'lng_masuk'         => $request->longitude,
                        'akurasi_gps_masuk' => round($request->accuracy),
                        'jarak_masuk'       => round($jarak),
                        'status_masuk'      => $status,
                        'foto_masuk'        => $path,
                        'ip_masuk'          => $request->ip(),
                        'rencana'           => $request->rencana,
                    ]
                );
            });
        } catch (\Throwable $e) {
            Storage::disk('public')->delete($path);
            return back()->with('error', 'Gagal menyimpan absen masuk. Silakan coba lagi.');
        }

        return redirect()->route('mahasiswa.absensi.index')
            ->with('success', 'Absen masuk berhasil dicatat pukul '.now()->format('H:i').
                " WIB. Jarak Anda dari instansi: {$jarak} meter.");
    }

    public function checkOut(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'latitude'  => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'accuracy'  => 'required|numeric|min:0',
            'foto'      => 'required|image|mimes:jpeg,jpg,png|max:4096',
            'realisasi'  => 'required|string|min:10|max:1000',
        ], [
            'foto.required' => 'Foto bukti absen wajib diambil melalui kamera.',
            'realisasi.required' => 'Realisasi kegiatan hari ini wajib diisi.', 
            'realisasi.min'      => 'Realisasi kegiatan minimal 10 karakter.',  
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator, 'absenKeluar')->withInput();
        }

        $mahasiswa = auth()->user()->mahasiswa;
        $instansi  = $mahasiswa?->instansi;

        if (!$instansi || is_null($instansi->latitude) || is_null($instansi->longitude)) {
            return back()->with('error', 'Lokasi instansi belum dapat diverifikasi. Hubungi admin.');
        }

        $today    = now()->format('Y-m-d');
        $absensi  = Absensi::where('mahasiswa_id', $mahasiswa->id)->where('tanggal', $today)->first();

        if (!$absensi || !$absensi->jam_masuk) {
            return back()->with('error', 'Anda belum melakukan absen masuk hari ini.');
        }

        if ($absensi->jam_keluar) {
            return back()->with('error', 'Anda sudah melakukan absen pulang hari ini pukul '.
                \Carbon\Carbon::parse($absensi->jam_keluar)->format('H:i').'.');
        }

        $jarak  = $this->hitungJarakMeter(
            (float) $request->latitude,
            (float) $request->longitude,
            (float) $instansi->latitude,
            (float) $instansi->longitude
        );
        // Jarak tetap dihitung & dicatat untuk transparansi/audit, tapi TIDAK
        // lagi dipakai untuk menggagalkan absen — status selalu "valid".
        $status = 'valid';

        $path = $request->file('foto')->store('absensi/'.$mahasiswa->id, 'public');

        try {
            DB::transaction(function () use ($absensi, $request, $jarak, $status, $path) {
                $absensi->update([
                    'jam_keluar'         => now()->format('H:i:s'),
                    'lat_keluar'         => $request->latitude,
                    'lng_keluar'         => $request->longitude,
                    'akurasi_gps_keluar' => round($request->accuracy),
                    'jarak_keluar'       => round($jarak),
                    'status_keluar'      => $status,
                    'foto_keluar'        => $path,
                    'ip_keluar'          => $request->ip(),
                    'realisasi'          => $request->realisasi,
                ]);
            });
        } catch (\Throwable $e) {
            Storage::disk('public')->delete($path);
            return back()->with('error', 'Gagal menyimpan absen pulang. Silakan coba lagi.');
        }

        return redirect()->route('mahasiswa.absensi.index')
            ->with('success', 'Absen pulang berhasil dicatat pukul '.now()->format('H:i').' WIB.');
    }

    /**
     * Hitung jarak dua titik koordinat menggunakan formula Haversine.
     * Selalu dihitung di server — nilai jarak dari client tidak pernah dipercaya.
     */
    private function hitungJarakMeter(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $radiusBumi = 6371000; // meter

        $lat1Rad   = deg2rad($lat1);
        $lat2Rad   = deg2rad($lat2);
        $deltaLat  = deg2rad($lat2 - $lat1);
        $deltaLng  = deg2rad($lng2 - $lng1);

        $a = sin($deltaLat / 2) ** 2
            + cos($lat1Rad) * cos($lat2Rad) * sin($deltaLng / 2) ** 2;
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $radiusBumi * $c;
    }
}
