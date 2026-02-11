<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Siswa extends Model
{
    protected $table = 'siswa';
    protected $primaryKey = 'id_siswa';
    protected $fillable = [
        'id_user', 
        'nis', 
        'nama_siswa', 
        'id_kelas', 
        'jenis_kelamin', 
        'status_aktif',
        'foto', // <--- Tambahkan ini agar bisa menyimpan nama file gambar
        'face_descriptor'
    ];

    protected $casts = [
        'status_aktif' => 'boolean',
        'face_descriptor' => 'array', // Cast JSON string ke Array PHP secara otomatis
    ];

    public function kelas()
    {
        return $this->belongsTo(Kelas::class, 'id_kelas', 'id_kelas');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user', 'id_user');
    }

    public function kehadiran()
    {
        return $this->hasMany(Kehadiran::class, 'id_siswa', 'id_siswa');
    }
}