<?php

namespace App\Http\Controllers\Instansi;

use App\Http\Controllers\Controller;
use App\Models\Chat;
use App\Models\ChatPesan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChatController extends Controller
{
    private function instansi()
    {
        return Auth::user()->instansi;
    }

    /** Daftar semua chat milik instansi ini */
    public function index()
    {
        $instansi = $this->instansi();

        $chats = Chat::where('instansi_id', $instansi->id)
            ->with(['dosen', 'mahasiswa', 'pesanTerakhir'])
            ->latest('updated_at')
            ->get()
            ->map(function ($chat) {
                $chat->unread = $chat->unreadCount('instansi');
                return $chat;
            });

        $dosens = $instansi->mahasiswas()->with('dosen')->get()
            ->pluck('dosen')->unique('id')->filter();

        return view('instansi.chat.index', compact('instansi', 'chats', 'dosens'));
    }

    /** Form buat chat baru */
    public function create()
    {
        $instansi   = $this->instansi();
        $mahasiswas = $instansi->mahasiswas()->with('dosen')->get();
        $dosens     = $mahasiswas->pluck('dosen')->unique('id')->filter();

        return view('instansi.chat.create', compact('instansi', 'mahasiswas', 'dosens'));
    }

    /** Simpan chat baru + pesan pertama */
    public function store(Request $request)
    {
        $instansi = $this->instansi();

        $request->validate([
            'dosen_id'     => 'required|exists:dosens,id',
            'mahasiswa_id' => 'nullable|exists:mahasiswas,id',
            'tipe'         => 'required|in:monitoring,pengaduan,umum',
            'subjek'       => 'required|string|max:255',
            'pesan'        => 'required|string',
        ]);

        $dosenValid = $instansi->mahasiswas()
            ->where('dosen_id', $request->dosen_id)
            ->exists();
        abort_unless($dosenValid, 403, 'Dosen tidak terdaftar di instansi ini.');

        $chat = Chat::create([
            'instansi_id'  => $instansi->id,
            'dosen_id'     => (int) $request->dosen_id,
            'mahasiswa_id' => $request->mahasiswa_id ? (int) $request->mahasiswa_id : null,
            'tipe'         => $request->tipe,
            'subjek'       => $request->subjek,
            'status'       => 'open',
        ]);

        ChatPesan::create([
            'chat_id'       => $chat->id,
            'pengirim_role' => 'instansi',
            'pengirim_id'   => $instansi->id,
            'pesan'         => $request->pesan,
        ]);

        return redirect()->route('instansi.chat.show', $chat)
            ->with('success', 'Pesan berhasil dikirim ke dosen.');
    }

    /** Detail chat & histori pesan */
    public function show(Chat $chat)
    {
        $instansi = $this->instansi();
        abort_if((int)$chat->instansi_id !== (int)$instansi->id, 403);

        $chat->pesans()
            ->where('pengirim_role', 'dosen')
            ->where('dibaca', false)
            ->update(['dibaca' => true]);

        $pesans = $chat->pesans()->oldest()->get();

        return view('instansi.chat.show', compact('chat', 'pesans', 'instansi'));
    }

    /** Kirim balasan */
    public function reply(Request $request, Chat $chat)
    {
        $instansi = $this->instansi();
        abort_if((int)$chat->instansi_id !== (int)$instansi->id, 403);
        abort_if($chat->status === 'closed', 403, 'Chat sudah ditutup.');

        $request->validate(['pesan' => 'required|string']);

        ChatPesan::create([
            'chat_id'       => $chat->id,
            'pengirim_role' => 'instansi',
            'pengirim_id'   => $instansi->id,
            'pesan'         => $request->pesan,
        ]);

        $chat->touch();

        return back()->with('success', 'Pesan terkirim.');
    }
}