<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Instansi extends Model
{
    protected $fillable = [
        'user_id', 'nama', 'bidang', 'alamat', 'kontak_person', 'no_hp',
        'latitude', 'longitude', 'radius_absen',
    ];

    protected $casts = [
        'latitude'     => 'float',
        'longitude'    => 'float',
        'radius_absen' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function mahasiswas()
    {
        return $this->hasMany(Mahasiswa::class);
    }

    public function chats()
    {
        return $this->hasMany(\App\Models\Chat::class);
    }
}