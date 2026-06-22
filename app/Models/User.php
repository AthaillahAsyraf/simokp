<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable {
    use HasFactory, Notifiable;
    protected $fillable = ['name', 'email', 'password', 'role'];
    protected $hidden   = ['password', 'remember_token'];
    protected $casts    = ['password' => 'hashed'];

    public function dosen()     { return $this->hasOne(Dosen::class); }
    public function instansi()  { return $this->hasOne(Instansi::class); }
    public function mahasiswa() { return $this->hasOne(Mahasiswa::class); }

    public function isAdmin()     { return $this->role === 'admin'; }
    public function isDosen()     { return $this->role === 'dosen'; }
    public function isInstansi()  { return $this->role === 'instansi'; }
    public function isMahasiswa() { return $this->role === 'mahasiswa'; }
}