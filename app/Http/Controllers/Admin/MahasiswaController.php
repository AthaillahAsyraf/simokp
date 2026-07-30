<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\{Mahasiswa, Dosen, Instansi, ProgressBab, User};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class MahasiswaController extends Controller {

    public function index(Request $request) {
        $query = Mahasiswa::with(['dosen','instansi.user','syaratAdministrasi']);
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
        $mahasiswa->load(['dosen','instansi','seminar']);
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
            'dosen_id'     => null,
            'instansi_id'  => null,
            'status'       => 'proses',
            'tahap'        => Mahasiswa::TAHAP_LENGKAPI_BERKAS,
        ]);
        $mhs->cekMajukanKeAktifKp();
        foreach (['BAB I','BAB II','BAB III','BAB IV','BAB V'] as $bab) {
            ProgressBab::create(['mahasiswa_id'=>$mhs->id,'bab'=>$bab,'status'=>'belum']);
        }
        return back()->with('success','Mahasiswa berhasil ditambahkan.');
    }

    public function update(Request $request, Mahasiswa $mahasiswa) {
        $request->validate(['nama'=>'required','angkatan'=>'required']);

        $mahasiswa->update($request->only(['nama','angkatan','no_hp','status']));

        return back()->with('success', 'Data mahasiswa diperbarui.');
    }

    public function tetapkanDosen(Request $request, Mahasiswa $mahasiswa) {
        $request->validate([
            'dosen_id' => 'required|exists:dosens,id',
        ]);

        if (!$mahasiswa->sudahMencapaiTahap(Mahasiswa::TAHAP_MENUNGGU_INSTANSI)) {
            return back()->with('error', "Berkas persyaratan {$mahasiswa->nama} belum disetujui. Dosen pembimbing belum dapat ditetapkan.");
        }

        $mahasiswa->update(['dosen_id' => $request->dosen_id]);
        $mahasiswa->cekMajukanKeAktifKp();

        $pesan = "Dosen pembimbing untuk {$mahasiswa->nama} berhasil ditetapkan.";
        if ($mahasiswa->fresh()->sudahAktifKp()) {
            $pesan .= ' Mahasiswa kini dapat memulai KP.';
        }

        return back()->with('success', $pesan);
    }

    public function destroy(Mahasiswa $mahasiswa) {
        $mahasiswa->user->delete();
        return back()->with('success','Mahasiswa berhasil dihapus.');
    }
}
