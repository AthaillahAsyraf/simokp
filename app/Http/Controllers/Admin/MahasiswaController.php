<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\{Mahasiswa, Dosen, Instansi, ProgressBab, User};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class MahasiswaController extends Controller {

    public function index(Request $request) {
        $query = Mahasiswa::with(['dosen','instansi','bimbingans','syaratAdministrasi']);
        if ($request->filled('search')) {
            $q = $request->search;
            $query->where(fn($q2) => $q2->where('nama','like',"%$q%")->orWhere('nim','like',"%$q%"));
        }
        if ($request->filled('status'))   $query->where('status', $request->status);
        if ($request->filled('dosen_id')) $query->where('dosen_id', $request->dosen_id);
        if ($request->filled('instansi_id')) $query->where('instansi_id', $request->instansi_id);
        if ($request->filled('tahap') && array_key_exists($request->tahap, Mahasiswa::LABEL_TAHAP)) {
            $query->where('tahap', $request->tahap);
        }

        $mahasiswas = $query
            ->orderByRaw("CASE tahap
                WHEN 'lengkapi_berkas' THEN 1
                WHEN 'menunggu_verifikasi' THEN 2
                WHEN 'revisi_berkas' THEN 3
                WHEN 'unggah_surat_balasan' THEN 4
                WHEN 'menunggu_instansi' THEN 5
                WHEN 'aktif_kp' THEN 6
                ELSE 99 END")
            ->orderBy('nama')
            ->get();
        $dosens     = Dosen::all();
        $instansis  = Instansi::all();
        $tahapans   = Mahasiswa::LABEL_TAHAP;

        return view('admin.mahasiswa.index', compact('mahasiswas','dosens','instansis','tahapans'));
    }

    public function show(Mahasiswa $mahasiswa) {
        $mahasiswa->load(['dosen','instansi','seminar','bimbingans']);
        return view('admin.mahasiswa.show', compact('mahasiswa'));
    }

    public function store(Request $request) {
        $request->validate([
            'nim'      => 'required|unique:mahasiswas,nim',
            'nama'     => 'required',
            'angkatan' => 'required',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|min:6',
        ]);
        $user = User::create([
            'name'    => $request->nama,
            'email'   => $request->email,
            'password'=> Hash::make($request->password),
            'role'    => 'mahasiswa',
        ]);
        $mhs = Mahasiswa::create([
            'user_id'      => $user->id,
            'nim'          => $request->nim,
            'nama'         => $request->nama,
            'angkatan'     => $request->angkatan,
            'no_hp'        => $request->no_hp,
            'dosen_id'     => $request->dosen_id ?: null,
            'instansi_id'  => $request->instansi_id ?: null,
            'status'       => 'proses',
            'tahap'        => ($request->dosen_id && $request->instansi_id) ? 'menunggu_instansi' : 'lengkapi_berkas',
        ]);
        $mhs->cekMajukanKeAktifKp();
        foreach (['BAB I','BAB II','BAB III','BAB IV','BAB V'] as $bab) {
            ProgressBab::create(['mahasiswa_id'=>$mhs->id,'bab'=>$bab,'status'=>'belum']);
        }
        return back()->with('success','Mahasiswa berhasil ditambahkan.');
    }

    public function update(Request $request, Mahasiswa $mahasiswa) {
        $request->validate(['nama'=>'required','angkatan'=>'required']);

        // Instansi/dosen baru cuma boleh diisi kalau berkas persyaratan mahasiswa
        // sudah disetujui (tahap >= menunggu_instansi), sesuai Prosedur KP.
        $akanSetInstansi = $request->filled('instansi_id') && !$mahasiswa->instansi_id;
        $akanSetDosen    = $request->filled('dosen_id') && !$mahasiswa->dosen_id;
        if (($akanSetInstansi || $akanSetDosen) && !$mahasiswa->sudahMencapaiTahap(Mahasiswa::TAHAP_MENUNGGU_INSTANSI)) {
            return back()->with('error', "Berkas persyaratan {$mahasiswa->nama} belum disetujui admin. Instansi/dosen belum bisa ditentukan dulu.")->withInput();
        }

        $mahasiswa->update($request->only(['nama','angkatan','no_hp','dosen_id','instansi_id','status']));
        $mahasiswa->cekMajukanKeAktifKp();

        $pesan = 'Data mahasiswa diperbarui.';
        if ($mahasiswa->tahap === Mahasiswa::TAHAP_MENUNGGU_KESEDIAAN_PEMBIMBING) {
            $pesan .= ' Form kesediaan pembimbing telah diterbitkan untuk diteruskan mahasiswa kepada dosen.';
        }

        return back()->with('success', $pesan);
    }

    public function destroy(Mahasiswa $mahasiswa) {
        $mahasiswa->user->delete();
        return back()->with('success','Mahasiswa berhasil dihapus.');
    }
}
