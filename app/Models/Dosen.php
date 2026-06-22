<?php
// ─── app/Models/Dosen.php ────────────────────────────────────────────────────
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Dosen extends Model {
    protected $fillable = ['user_id', 'nip', 'nama', 'no_hp'];
    public function user()       { return $this->belongsTo(User::class); }
    public function mahasiswas() { return $this->hasMany(Mahasiswa::class); }
    public function chats()      { return $this->hasMany(\App\Models\Chat::class); }
}