<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\Instansi;
use App\Models\Mahasiswa;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

/**
 * Mahasiswa mendaftarkan instansi tempat KP beserta Pembimbing Lapangannya.
 * Pembimbing Lapangan diperlakukan seperti Dosen Pembimbing: satu akun bisa
 * membimbing banyak mahasiswa. Mahasiswa bisa memilih pembimbing lapangan
 * yang SUDAH ADA (dropdown, kalau kebetulan instansi/PIC-nya sama dengan
 * mahasiswa lain), atau membuat akun baru sekaligus.
 */
class InstansiController extends Controller
{
    public function index()
    {
        $mahasiswa = Auth::user()->mahasiswa;

        if ($mahasiswa->instansi_id) {
            return redirect()->route('mahasiswa.dashboard')
                ->with('error', 'Anda sudah terdaftar di instansi KP. Pendaftaran instansi hanya dapat dilakukan satu kali.');
        }

        $instansis = Instansi::orderBy('nama')->get();

        return view('mahasiswa.instansi.index', compact('mahasiswa', 'instansis'));
    }

    public function store(Request $request)
    {
        $mahasiswa = Auth::user()->mahasiswa;

        // Jangan hanya mengandalkan halaman/form: request POST yang dikirim
        // ulang atau dibuat manual juga tidak boleh mengganti instansi mahasiswa.
        if ($mahasiswa->instansi_id) {
            return redirect()->route('mahasiswa.dashboard')
                ->with('error', 'Anda sudah terdaftar di instansi KP. Pendaftaran instansi hanya dapat dilakukan satu kali.');
        }

        if (!$mahasiswa->sudahMencapaiTahap(Mahasiswa::TAHAP_MENUNGGU_INSTANSI)) {
            return back()->with('error', 'Tahap ini belum bisa diakses. Selesaikan tahap sebelumnya terlebih dahulu.');
        }

        $mode = $request->input('mode', 'pilih'); // 'pilih' | 'baru'

        if ($mode === 'pilih') {
            return $this->simpanDenganInstansiExisting($request, $mahasiswa);
        }

        return $this->simpanDenganInstansiBaru($request, $mahasiswa);
    }

    private function simpanDenganInstansiExisting(Request $request, Mahasiswa $mahasiswa)
    {
        $validator = Validator::make($request->all(), [
            'instansi_id' => 'required|exists:instansis,id',
        ], [
            'instansi_id.required' => 'Silakan pilih instansi & pembimbing lapangan terlebih dahulu.',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator, 'daftarInstansi')->withInput();
        }

        $instansi = Instansi::findOrFail($request->instansi_id);

        $mahasiswa->update([
            'instansi_id'                 => $instansi->id,
            'pembimbing_lapangan_nama'    => $instansi->kontak_person ?: $instansi->nama,
            'pembimbing_lapangan_jabatan' => $instansi->bidang,
            'pembimbing_lapangan_no_hp'   => $instansi->no_hp,
        ]);

        $mahasiswa->cekMajukanKeAktifKp();

        return redirect()->route('mahasiswa.dashboard')
            ->with('success', "Instansi & Pembimbing Lapangan ({$instansi->nama}) berhasil didaftarkan.");
    }

    private function simpanDenganInstansiBaru(Request $request, Mahasiswa $mahasiswa)
    {
        $validator = Validator::make($request->all(), [
            'nama'          => 'required|string|max:255',
            'bidang'        => 'nullable|string|max:255',
            'alamat'        => 'nullable|string',
            'kontak_person' => 'required|string|max:255',
            'no_hp'         => 'nullable|string|max:20',
            'email'         => 'required|email|unique:users,email',
            'password'      => 'required|min:8|confirmed',
            'latitude'      => 'required|numeric|between:-90,90',
            'longitude'     => 'required|numeric|between:-180,180',
        ], [
            'kontak_person.required' => 'Nama Pembimbing Lapangan (PIC) wajib diisi.',
            'email.unique'           => 'Email ini sudah dipakai akun lain. Kalau pembimbing lapangan ini sudah pernah didaftarkan mahasiswa lain, pilih dari daftar yang sudah ada saja.',
            'latitude.required'      => 'Titik lokasi instansi wajib diisi (dipakai untuk validasi absen GPS Anda nanti). Cari lokasi instansi di Google Maps, salin link "Bagikan"-nya, lalu tempel di kolom yang tersedia.',
            'longitude.required'     => 'Titik lokasi instansi wajib diisi.',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator, 'daftarInstansiBaru')->withInput();
        }

        try {
            $instansi = DB::transaction(function () use ($request) {
                $user = User::create([
                    'name'                 => $request->kontak_person,
                    'email'                => $request->email,
                    'password'             => Hash::make($request->password),
                    'role'                 => 'pembimbing_lapangan',
                    'wajib_ganti_password' => true,
                ]);

                return Instansi::create([
                    'user_id'       => $user->id,
                    'nama'          => $request->nama,
                    'bidang'        => $request->bidang,
                    'alamat'        => $request->alamat,
                    'kontak_person' => $request->kontak_person,
                    'no_hp'         => $request->no_hp,
                    'latitude'      => $request->latitude,
                    'longitude'     => $request->longitude,
                ]);
            });
        } catch (\Throwable $e) {
            return back()->with('db_error', 'Gagal menyimpan data instansi: '.$e->getMessage())->withInput();
        }

        $mahasiswa->update([
            'instansi_id'                 => $instansi->id,
            'pembimbing_lapangan_nama'    => $instansi->kontak_person,
            'pembimbing_lapangan_jabatan' => $instansi->bidang,
            'pembimbing_lapangan_no_hp'   => $instansi->no_hp,
        ]);

        $mahasiswa->cekMajukanKeAktifKp();

        return redirect()->route('mahasiswa.dashboard')
            ->with('success', "Instansi {$instansi->nama} & akun Pembimbing Lapangan berhasil dibuat. Sampaikan email ({$request->email}) dan password yang tadi Anda buat kepada pembimbing lapangan Anda secara langsung — mereka wajib menggantinya saat login pertama.");
    }
}
