<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DetailAbsensi extends Model
{
    protected $table = 'detail_absensi';
    protected $primaryKey = 'id_detail';
    
    // Aktifkan timestamps jika tabel Anda memilikinya, 
    // namun jika benar-benar tidak ada kolom created_at, biarkan false.
    public $timestamps = false; 

    protected $fillable = [
        'id_absensi', 
        'id_siswa', 
        'status', 
        'waktu_scan', 
        'keterangan', // Tambahan
    ];

    /**
     * Casting kolom agar otomatis menjadi objek Carbon (Datetime)
     */
    protected $casts = [
        'waktu_scan' => 'datetime',
    ];

    // Relasi ke tabel Siswa
    public function siswa() {
        return $this->belongsTo(Siswa::class, 'id_siswa', 'id_siswa');
    }

    // Relasi ke tabel Absensi (Sesi)
    public function absensi() {
        return $this->belongsTo(Absensi::class, 'id_absensi', 'id_absensi');
    }
}