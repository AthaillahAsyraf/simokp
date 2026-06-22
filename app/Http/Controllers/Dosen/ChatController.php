<?php

namespace App\Http\Controllers\Dosen;

use App\Http\Controllers\Controller;
use App\Models\Chat;
use App\Models\ChatPesan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChatController extends Controller
{
    private function dosen()
    {
        return Auth::user()->dosen;
    }

    /** Daftar semua chat masuk untuk dosen ini */
    public function index()
    {
        $dosen = $this->dosen();

        $chats = Chat::where('dosen_id', $dosen->id)
            ->with(['instansi', 'mahasiswa', 'pesanTerakhir'])
            ->latest('updated_at')
            ->get()
            ->map(function ($chat) {
                $chat->unread = $chat->unreadCount('dosen');
                return $chat;
            });

        return view('dosen.chat.index', compact('dosen', 'chats'));
    }

    /** Detail chat & balas */
    public function show(Chat $chat)
    {
        $dosen = $this->dosen();
        abort_if((int)$chat->dosen_id !== (int)$dosen->id, 403);

        // tandai pesan dari instansi sudah dibaca
        $chat->pesans()
            ->where('pengirim_role', 'instansi')
            ->where('dibaca', false)
            ->update(['dibaca' => true]);

        $pesans = $chat->pesans()->oldest()->get();

        return view('dosen.chat.show', compact('chat', 'pesans', 'dosen'));
    }

    /** Balas dari sisi dosen */
    public function reply(Request $request, Chat $chat)
    {
        $dosen = $this->dosen();
        abort_if((int)$chat->dosen_id !== (int)$dosen->id, 403);
        abort_if($chat->status === 'closed', 403, 'Chat sudah ditutup.');

        $request->validate(['pesan' => 'required|string']);

        ChatPesan::create([
            'chat_id'       => $chat->id,
            'pengirim_role' => 'dosen',
            'pengirim_id'   => $dosen->id,
            'pesan'         => $request->pesan,
        ]);

        $chat->touch();

        return back()->with('success', 'Balasan terkirim.');
    }

    /** Tutup / selesaikan chat */
    public function close(Chat $chat)
    {
        $dosen = $this->dosen();
        abort_if((int)$chat->dosen_id !== (int)$dosen->id, 403);

        $chat->update(['status' => 'closed']);

        return back()->with('success', 'Chat ditandai selesai.');
    }
}