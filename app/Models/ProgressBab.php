<?php
// app/Models/ProgressBab.php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class ProgressBab extends Model
{
    protected $fillable = ['mahasiswa_id', 'bab', 'status', 'tanggal_update', 'catatan'];

    public function mahasiswa() { return $this->belongsTo(Mahasiswa::class); }
}