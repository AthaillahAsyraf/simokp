<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\{Dosen, User};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Hash, Validator, DB};

class DosenController extends Controller {
    /**
     * Halaman index lama sekarang digabung ke menu "Pembimbing" (tab Dosen).
     * Redirect supaya link lama (dashboard, tombol kembali di show.blade.php) tetap jalan.
     */
    public function index() {
        return redirect()->route('admin.pembimbing.index', ['tab' => 'dosen']);
    }

    public function show(Dosen $dosen) {
        $dosen->load(['mahasiswas.instansi','mahasiswas.progressBabs']);
        return view('admin.dosen.show', compact('dosen'));
    }

    public function store(Request $request) {
        $validator = Validator::make($request->all(), [
            'nip'      => 'required|numeric|unique:dosens,nip',
            'nama'     => 'required|string|max:255',
            'no_hp'    => 'nullable|numeric',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|min:8',
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
                    'role'     => 'dosen',
                ]);
                Dosen::create([
                    'user_id' => $user->id,
                    'nip'     => $request->nip,
                    'nama'    => $request->nama,
                    'no_hp'   => $request->no_hp,
                ]);
            });
        } catch (\Throwable $e) {
            return back()->with('db_error', 'Gagal menambahkan dosen. Silakan coba lagi.')->withInput();
        }

        return redirect()->route('admin.pembimbing.index', ['tab' => 'dosen'])
            ->with('success', 'Dosen berhasil ditambahkan.');
    }

    public function update(Request $request, Dosen $dosen) {
        $validator = Validator::make($request->all(), [
            'nip'   => 'required|numeric|unique:dosens,nip,'.$dosen->id,
            'nama'  => 'required|string|max:255',
            'no_hp' => 'nullable|numeric',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator, 'edit')->withInput()->with('edit_id', $dosen->id);
        }

        try {
            $dosen->update($request->only(['nip','nama','no_hp']));
        } catch (\Throwable $e) {
            return back()->with('db_error', 'Gagal menyimpan perubahan dosen.')->withInput()->with('edit_id', $dosen->id);
        }

        return redirect()->route('admin.pembimbing.index', ['tab' => 'dosen'])
            ->with('success', 'Data dosen diperbarui.');
    }

    public function destroy(Dosen $dosen) {
        try {
            $dosen->user?->delete();
        } catch (\Throwable $e) {
            return back()->with('db_error', 'Gagal menghapus dosen. Pastikan dosen tidak sedang membimbing mahasiswa aktif.');
        }
        return redirect()->route('admin.pembimbing.index', ['tab' => 'dosen'])
            ->with('success', 'Dosen berhasil dihapus.');
    }
}