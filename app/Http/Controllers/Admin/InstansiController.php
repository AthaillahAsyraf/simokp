<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{Instansi, User};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class InstansiController extends Controller
{
    public function index()
    {
        $instansis = Instansi::with(['user', 'mahasiswas'])->latest()->get();
        return view('admin.instansi.index', compact('instansis'));
    }

    public function show(Instansi $instansi)
    {
        $instansi->load(['mahasiswas.dosen', 'mahasiswas.progressBabs']);
        return view('admin.instansi.show', compact('instansi'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nama'          => 'required|string|max:255',
            'bidang'        => 'nullable|string|max:255',
            'alamat'        => 'nullable|string',
            'kontak_person' => 'nullable|string|max:255',
            'no_hp'         => 'nullable|numeric',
            'email'         => 'required|email|unique:users,email',
            'password'      => 'required|min:8',
            'latitude'      => 'nullable|numeric|between:-90,90',
            'longitude'     => 'nullable|numeric|between:-180,180',
            'radius_absen'  => 'nullable|integer|min:10|max:5000',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator, 'tambah')->withInput();
        }

        try {
            DB::transaction(function () use ($request) {
                $user = User::create([
                    'name'     => $request->nama,
                    'email'    => $request->email,
                    'password' => Hash::make($request->password),
                    'role'     => 'instansi',
                ]);

                Instansi::create([
                    'user_id'       => $user->id,
                    'nama'          => $request->nama,
                    'bidang'        => $request->bidang,
                    'alamat'        => $request->alamat,
                    'kontak_person' => $request->kontak_person,
                    'no_hp'         => $request->no_hp,
                    'latitude'      => $request->latitude,
                    'longitude'     => $request->longitude,
                    'radius_absen'  => $request->radius_absen ?: 100,
                ]);
            });
        } catch (\Throwable $e) {
            return back()->with('db_error', 'Gagal menyimpan instansi: '.$e->getMessage())->withInput();
        }

        return redirect()->route('admin.instansi.index')->with('success', 'Instansi berhasil ditambahkan.');
    }

    public function update(Request $request, Instansi $instansi)
    {
        $validator = Validator::make($request->all(), [
            'nama'          => 'required|string|max:255',
            'bidang'        => 'nullable|string|max:255',
            'alamat'        => 'nullable|string',
            'kontak_person' => 'nullable|string|max:255',
            'no_hp'         => 'nullable|numeric',
            'latitude'      => 'nullable|numeric|between:-90,90',
            'longitude'     => 'nullable|numeric|between:-180,180',
            'radius_absen'  => 'nullable|integer|min:10|max:5000',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator, 'edit')->withInput()->with('edit_id', $instansi->id);
        }

        try {
            $instansi->update($request->only([
                'nama', 'bidang', 'alamat', 'kontak_person', 'no_hp',
                'latitude', 'longitude', 'radius_absen',
            ]));
        } catch (\Throwable $e) {
            return back()->with('db_error', 'Gagal memperbarui instansi: '.$e->getMessage())
                ->withInput()->with('edit_id', $instansi->id);
        }

        return redirect()->route('admin.instansi.index')->with('success', 'Data instansi diperbarui.');
    }

    public function destroy(Instansi $instansi)
    {
        try {
            DB::transaction(function () use ($instansi) {
                $instansi->user?->delete(); // null-safe: instansi mungkin sudah tidak punya user
                $instansi->delete();
            });
        } catch (\Throwable $e) {
            return back()->with('db_error', 'Gagal menghapus instansi: '.$e->getMessage());
        }

        return redirect()->route('admin.instansi.index')->with('success', 'Instansi berhasil dihapus.');
    }

    /**
     * Ekstrak koordinat lat/lng dari sebuah link Google Maps yang ditempel user.
     * Mendukung link panjang (langsung diparse dari string-nya) maupun link
     * pendek (maps.app.goo.gl / goo.gl) yang perlu ditelusuri redirect-nya
     * dulu di server (karena browser tidak bisa fetch cross-origin ke sana).
     */
    public function resolveLokasi(Request $request)
    {
        $request->validate([
            'link' => 'required|string|max:2048',
        ]);

        $link = trim($request->link);

        // 1) Coba ekstrak langsung dari string link (link Maps versi panjang
        //    sudah mengandung koordinat di URL-nya, tidak perlu request keluar).
        $coords = $this->extractLatLng($link);

        // 2) Kalau belum ketemu, kemungkinan ini link pendek yang perlu
        //    ditelusuri redirect-nya. Batasi HANYA ke domain Google Maps
        //    yang dikenal supaya endpoint ini tidak disalahgunakan untuk
        //    memanggil URL sembarangan dari server (SSRF).
        if (!$coords) {
            if (!$this->isAllowedMapsHost($link)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Link harus berupa link Google Maps (google.com/maps, maps.app.goo.gl, atau goo.gl).',
                ], 422);
            }

            $finalUrl = $this->resolveFinalUrl($link);
            if ($finalUrl) {
                $coords = $this->extractLatLng($finalUrl);
            }
        }

        if (!$coords) {
            return response()->json([
                'success' => false,
                'message' => 'Koordinat tidak ditemukan pada link tersebut. Pastikan link berasal dari tombol "Bagikan" di Google Maps, atau isi koordinat secara manual.',
            ], 422);
        }

        return response()->json([
            'success'   => true,
            'latitude'  => $coords['lat'],
            'longitude' => $coords['lng'],
        ]);
    }

    private function extractLatLng(string $url): ?array
    {
        // Format: .../@-5.4291839,105.2618658,17z
        if (preg_match('/@(-?\d{1,3}\.\d+),(-?\d{1,3}\.\d+)/', $url, $m)) {
            return ['lat' => $m[1], 'lng' => $m[2]];
        }
        // Format: ?q=-5.4291839,105.2618658 atau ?query=-5.4291839,105.2618658
        if (preg_match('/[?&](?:q|query)=(-?\d{1,3}\.\d+),(-?\d{1,3}\.\d+)/', $url, $m)) {
            return ['lat' => $m[1], 'lng' => $m[2]];
        }
        // Format: ?ll=-5.4291839,105.2618658
        if (preg_match('/[?&]ll=(-?\d{1,3}\.\d+),(-?\d{1,3}\.\d+)/', $url, $m)) {
            return ['lat' => $m[1], 'lng' => $m[2]];
        }
        // Format data-blob Google Maps: !3d-5.4291839!4d105.2618658
        if (preg_match('/!3d(-?\d{1,3}\.\d+)!4d(-?\d{1,3}\.\d+)/', $url, $m)) {
            return ['lat' => $m[1], 'lng' => $m[2]];
        }

        return null;
    }

    private function isAllowedMapsHost(string $url): bool
    {
        $parts = parse_url($url);
        if (!$parts || empty($parts['host']) || empty($parts['scheme'])) {
            return false;
        }
        if (!in_array(strtolower($parts['scheme']), ['http', 'https'], true)) {
            return false;
        }

        $host = strtolower($parts['host']);
        $allowed = [
            'maps.google.com', 'www.google.com', 'google.com',
            'maps.app.goo.gl', 'goo.gl', 'g.co',
        ];

        foreach ($allowed as $a) {
            if ($host === $a || str_ends_with($host, '.'.$a)) {
                return true;
            }
        }

        return false;
    }

    private function resolveFinalUrl(string $url): ?string
    {
        if (!function_exists('curl_init')) {
            return null;
        }

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS      => 10,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 8,
            CURLOPT_USERAGENT      => 'Mozilla/5.0 (compatible; KP-System/1.0)',
        ]);
        curl_exec($ch);

        if (curl_errno($ch)) {
            curl_close($ch);
            return null;
        }

        $finalUrl = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
        curl_close($ch);

        return $finalUrl ?: null;
    }
}