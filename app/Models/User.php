<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = ['name', 'email', 'password', 'role'];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = ['password' => 'hashed'];

    // Relasi ke tiap profil
    public function dosen()
    {
        return $this->hasOne(Dosen::class);
    }

    public function instansi()
    {
        return $this->hasOne(Instansi::class);
    }

    public function mahasiswa()
    {
        return $this->hasOne(Mahasiswa::class);
    }

    // Helper role check
    public function isAdmin(): bool    { return $this->role === 'admin'; }
    public function isDosen(): bool    { return $this->role === 'dosen'; }
    public function isInstansi(): bool { return $this->role === 'instansi'; }
    public function isMahasiswa(): bool{ return $this->role === 'mahasiswa'; }
}