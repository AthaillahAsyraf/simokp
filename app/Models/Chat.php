<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Chat extends Model
{
    protected $fillable = [
        'instansi_id', 'dosen_id', 'mahasiswa_id', 'subjek', 'tipe', 'status',
    ];

    public function instansi()   { return $this->belongsTo(Instansi::class); }
    public function dosen()      { return $this->belongsTo(Dosen::class); }
    public function mahasiswa()  { return $this->belongsTo(Mahasiswa::class); }
    public function pesans()     { return $this->hasMany(ChatPesan::class); }

    public function pesanTerakhir()
    {
        return $this->hasOne(ChatPesan::class)->latestOfMany();
    }

    /** Jumlah pesan belum dibaca untuk role tertentu */
    public function unreadCount(string $role): int
    {
        return $this->pesans()
            ->where('dibaca', false)
            ->where('pengirim_role', '!=', $role)
            ->count();
    }
}