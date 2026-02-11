<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Kehadiran extends Model
{
    protected $primaryKey = 'id_kehadiran';

    protected $table = 'kehadiran';
    protected $fillable = [
        'id_absensi', 
        'id_siswa', 
        'waktu_absen', 
        'status_kehadiran', 
        'lampiran_foto', 
        'keterangan'
    ];

    public function absensi() {
        return $this->belongsTo(Absensi::class, 'id_absensi');
    }

    public function siswa() {
        return $this->belongsTo(Siswa::class, 'id_siswa');
    }
}
