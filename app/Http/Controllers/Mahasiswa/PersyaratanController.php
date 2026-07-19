<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\Mahasiswa;
use App\Models\SyaratAdministrasi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class PersyaratanController extends Controller
{
    public function index()
    {
        $mahasiswa = Auth::user()->mahasiswa->load(['dosen', 'instansi']);
        $syarat    = $mahasiswa->syaratAdministrasi ?? new SyaratAdministrasi(['mahasiswa_id' => $mahasiswa->id]);

        return view('mahasiswa.persyaratan.index', compact('mahasiswa', 'syarat'));
    }

    /**
     * Upload/re-upload berkas persyaratan. Boleh dipakai untuk:
     * - upload pertama kali (tahap lengkapi_berkas)
     * - upload ulang saat direvisi admin (tahap revisi_berkas)
     * Field yang tidak diisi ulang akan tetap pakai file lama (partial update).
     */
    public function upload(Request $request)
    {
        $mahasiswa = Auth::user()->mahasiswa;

       if (in_array($mahasiswa->tahap, [Mahasiswa::TAHAP_MENUNGGU_VERIFIKASI, Mahasiswa::TAHAP_UNGGAH_SURAT_BALASAN, Mahasiswa::TAHAP_MENUNGGU_INSTANSI, Mahasiswa::TAHAP_AKTIF_KP])) {
            return back()->with('error', 'Berkas Anda sudah terkirim/disetujui, tidak perlu upload ulang.');
        }

        $rules = [];
        foreach (array_keys(SyaratAdministrasi::BERKAS) as $field) {
            $rules[$field] = 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120';
        }

        $validator = Validator::make($request->all(), $rules, [
            '*.mimes' => 'File harus berformat PDF, JPG, atau PNG.',
            '*.max'   => 'Ukuran file maksimal 5MB.',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator, 'upload')->withInput();
        }

        try {
            DB::transaction(function () use ($request, $mahasiswa) {
                $syarat = SyaratAdministrasi::firstOrCreate(['mahasiswa_id' => $mahasiswa->id]);

                $data = [];
                foreach (array_keys(SyaratAdministrasi::BERKAS) as $field) {
                    if ($request->hasFile($field)) {
                        if ($syarat->$field) {
                            Storage::disk('public')->delete($syarat->$field);
                        }
                        $uploaded = $request->file($field);
                        $data[$field] = $uploaded->store('syarat_administrasi/'.$mahasiswa->id, 'public');
                        $data[$field.'_asli'] = $uploaded->getClientOriginalName();
                    }
                }

                $data['status']  = SyaratAdministrasi::STATUS_BELUM_LENGKAP;
                $data['catatan'] = null;

                $syarat->update($data);

                if ($syarat->fresh()->isLengkap()) {
                    $syarat->update([
                        'status'       => SyaratAdministrasi::STATUS_MENUNGGU_VERIFIKASI,
                        'submitted_at' => now(),
                    ]);
                    $mahasiswa->update(['tahap' => Mahasiswa::TAHAP_MENUNGGU_VERIFIKASI]);
                }
            });
        } catch (\Throwable $e) {
            return back()->with('db_error', 'Gagal mengupload berkas. Silakan coba lagi.');
        }

        $syarat = $mahasiswa->fresh()->syaratAdministrasi;
        $msg = $syarat->isLengkap()
            ? 'Semua berkas lengkap, terkirim dan menunggu diverifikasi admin jurusan.'
            : 'Berkas tersimpan. Lengkapi berkas yang masih kosong sebelum bisa diverifikasi admin.';

        return back()->with('success', $msg);
    }
}