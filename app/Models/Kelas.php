<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Kelas extends Model
{
    protected $table = 'kelas';
    protected $primaryKey = 'id_kelas';
    protected $fillable = ['id_jurusan', 'nama_kelas', 'tingkat', 'api_kelas_id'];

    // Relasi ke Jurusan
    public function jurusan()
    {
        return $this->belongsTo(Jurusan::class, 'id_jurusan', 'id_jurusan');
    }

    // Relasi ke Siswa
    public function siswa()
    {
        return $this->hasMany(Siswa::class, 'id_kelas', 'id_kelas');
    }

    // Relasi ke Sesi Absensi
    public function absensi()
    {
        return $this->hasMany(Absensi::class, 'id_kelas', 'id_kelas');
    }
}
