<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\{Mahasiswa, Dosen, Instansi, ProgressBab, User};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class MahasiswaController extends Controller {

    public function index(Request $request) {
        $query = Mahasiswa::with(['user', 'dosen', 'instansi']);
        if ($request->filled('search')) {
            $q = $request->search;
            $query->where(fn($q2) => $q2
                ->where('nama','like',"%$q%")
                ->orWhere('nim','like',"%$q%")
                ->orWhereHas('user', fn($user) => $user->where('email', 'like', "%$q%")));
        }
        if ($request->filled('status'))   $query->where('status', $request->status);
        if ($request->filled('angkatan')) $query->where('angkatan', $request->angkatan);

        $mahasiswas = $query->orderBy('nama')->get();
        $angkatans = Mahasiswa::whereNotNull('angkatan')->distinct()->orderByDesc('angkatan')->pluck('angkatan');

        return view('admin.mahasiswa.index', compact('mahasiswas', 'angkatans'));
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
        $request->validate([
            'nama' => 'required|string|max:255',
            'angkatan' => 'required',
            'email' => 'required|email|unique:users,email,'.$mahasiswa->user_id,
            'password' => 'nullable|min:8|confirmed',
        ]);

        $data = $request->only(['nama','angkatan','no_hp','status']);
        if (($data['status'] ?? null) === 'selesai') {
            $data['tahap'] = Mahasiswa::TAHAP_SELESAI_KP;
        } elseif ($mahasiswa->tahap === Mahasiswa::TAHAP_SELESAI_KP) {
            $data['tahap'] = Mahasiswa::TAHAP_AKTIF_KP;
        }

        $mahasiswa->update($data);
        $mahasiswa->user->update(array_filter([
            'name' => $request->nama,
            'email' => $request->email,
            'password' => $request->filled('password') ? Hash::make($request->password) : null,
        ], fn ($value) => $value !== null));

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
