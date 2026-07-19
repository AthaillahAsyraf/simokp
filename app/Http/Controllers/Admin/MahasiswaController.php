<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\{Mahasiswa, Dosen, Instansi, ProgressBab, User};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class MahasiswaController extends Controller {

    public function index(Request $request) {
        $query = Mahasiswa::with(['dosen','instansi','progressBabs']);
        if ($request->filled('search')) {
            $q = $request->search;
            $query->where(fn($q2) => $q2->where('nama','like',"%$q%")->orWhere('nim','like',"%$q%"));
        }
        if ($request->filled('status'))   $query->where('status', $request->status);
        if ($request->filled('dosen_id')) $query->where('dosen_id', $request->dosen_id);
        if ($request->filled('instansi_id')) $query->where('instansi_id', $request->instansi_id);

        $mahasiswas = $query->latest()->paginate(10)->withQueryString();
        $dosens     = Dosen::all();
        $instansis  = Instansi::all();
        return view('admin.mahasiswa.index', compact('mahasiswas','dosens','instansis'));
    }

    public function show(Mahasiswa $mahasiswa) {
        $mahasiswa->load(['dosen','instansi','progressBabs','seminar']);
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
            'tanggal_mulai'=> $request->tanggal_mulai,
            'status'       => 'proses',
            // Kalau admin langsung isi dosen & instansi saat membuat akun (mis.
            // berkas sudah diurus manual/offline), langsung aktifkan KP-nya.
            // Kalau tidak, mahasiswa mulai dari tahap lengkapi berkas seperti biasa.
            'tahap'        => ($request->dosen_id && $request->instansi_id) ? 'aktif_kp' : 'lengkapi_berkas',
        ]);
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
            return back()->with('error', "Berkas persyaratan {$mahasiswa->nama} belum disetujui admin (lihat menu Persyaratan KP). Instansi/dosen belum bisa ditentukan dulu.")->withInput();
        }

        $mahasiswa->update($request->only(['nama','angkatan','no_hp','dosen_id','instansi_id','tanggal_mulai','tanggal_selesai','status']));
        $mahasiswa->cekMajukanKeAktifKp();

        return back()->with('success','Data mahasiswa diperbarui.');
    }

    public function destroy(Mahasiswa $mahasiswa) {
        $mahasiswa->user->delete();
        return back()->with('success','Mahasiswa berhasil dihapus.');
    }
}