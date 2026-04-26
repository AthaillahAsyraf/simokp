<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Instansi extends Model
{
    protected $fillable = ['user_id', 'nama', 'bidang', 'alamat', 'kontak_person', 'no_hp'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function mahasiswas()
    {
        return $this->hasMany(Mahasiswa::class);
    }
}