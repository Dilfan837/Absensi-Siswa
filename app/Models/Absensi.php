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

    public function guru() {
        // Asumsi 'dibuat_oleh' di tabel absensi merujuk ke 'id_user' pada tabel users, 
        // yang mana user tersebut adalah guru.
        return $this->belongsTo(Guru::class, 'dibuat_oleh', 'id_user');
    }

    public function mataPelajaran() {
        // Absensi -> Guru -> MataPelajaran
        return $this->hasOneThrough(MataPelajaran::class, Guru::class, 'id_user', 'id_mapel', 'dibuat_oleh', 'id_mapel');
    }

    public function details() {
        return $this->hasMany(DetailAbsensi::class, 'id_absensi', 'id_absensi');
    }

    public function detailAbsensis() {
        return $this->hasMany(DetailAbsensi::class, 'id_absensi', 'id_absensi');
    }
}