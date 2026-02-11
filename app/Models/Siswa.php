<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Siswa extends Model
{
    protected $table = 'siswa';
    protected $primaryKey = 'id_siswa';
    protected $fillable = [
        'id_user',
        'peserta_didik_id',
        'nis',
        'nisn',
        'nik',
        'no_induk',
        'nama_siswa',
        'id_kelas',
        'jenis_kelamin',
        'tempat_lahir',
        'tanggal_lahir',
        'agama_id',
        'anak_ke',
        'email',
        'no_telp',
        'alamat',
        'rt',
        'rw',
        'desa_kelurahan',
        'kecamatan',
        'kode_pos',
        'kode_wilayah',
        'sekolah_asal',
        'diterima_kelas_smk',
        'nama_rombel',
        'rombel_id',
        'nama_ayah',
        'nama_ibu',
        'kerja_ayah_id',
        'kerja_ibu_id',
        'nama_wali',
        'alamat_wali',
        'telp_wali',
        'kerja_wali_id',
        'status_aktif',
        'foto',
        'face_descriptor',
        'last_synced_at'
    ];

    protected $casts = [
        'status_aktif' => 'boolean',
        'face_descriptor' => 'array',
        'tanggal_lahir' => 'date',
        'last_synced_at' => 'datetime',
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