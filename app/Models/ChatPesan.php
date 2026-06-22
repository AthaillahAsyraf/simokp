<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatPesan extends Model
{
    protected $table = 'chat_pesan';

    protected $fillable = [
        'chat_id', 'pengirim_role', 'pengirim_id', 'pesan', 'dibaca',
    ];

    public function chat() { return $this->belongsTo(Chat::class); }

    /** Nama pengirim (resolves instansi atau dosen) */
    public function namaPengirim(): string
    {
        if ($this->pengirim_role === 'instansi') {
            return Instansi::find($this->pengirim_id)?->nama ?? 'Instansi';
        }
        return Dosen::find($this->pengirim_id)?->nama ?? 'Dosen';
    }
}