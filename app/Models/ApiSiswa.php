<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApiSiswa extends Model
{
    protected $table = 'api_siswas';
    
    protected $fillable = [
        'peserta_didik_id',
        'sekolah_id',
        'nama',
        'no_induk',
        'nisn',
        'nik',
        'jenis_kelamin',
        'tempat_lahir',
        'tanggal_lahir',
        'agama_id',
        'anak_ke',
        'alamat',
        'rt',
        'rw',
        'desa_kelurahan',
        'kecamatan',
        'kode_pos',
        'no_telp',
        'sekolah_asal',
        'diterima_kelas',
        'diterima_kelas_smk',
        'rombel_id',
        'nama_rombel',
        'nama_ayah',
        'nama_ibu',
        'nama_wali',
        'is_activated'
    ];

    protected $casts = [
        'is_activated' => 'boolean',
    ];
}
