<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Absensi extends Model
{
    protected $table = 'absensi';
    protected $primaryKey = 'id_absensi';

    // Sesuaikan persis dengan kolom di Migration kamu
    protected $fillable = [
        'id_kelas', 
        'dibuat_oleh', 
        'nama_absensi', 
        'tanggal', 
        'jam_mulai', 
        'jam_selesai', 
        'qr_token', 
        'status'
    ];

    public function kelas() {
        return $this->belongsTo(Kelas::class, 'id_kelas', 'id_kelas');
    }

    public function details() {
        return $this->hasMany(DetailAbsensi::class, 'id_absensi', 'id_absensi');
    }
}