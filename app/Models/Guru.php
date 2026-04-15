<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Guru extends Model
{
    protected $table = 'guru';
    protected $primaryKey = 'id_guru';
    protected $fillable = [
        'id_user', 'nuptk', 'nip', 'nik', 'nama', 'jenis_kelamin',
        'tempat_lahir', 'tanggal_lahir', 'agama_id',
        'email', 'no_hp', 'alamat',
        'rt', 'rw', 'desa_kelurahan', 'kecamatan', 'kode_wilayah', 'kode_pos',
        'photo', 'status_aktif', 'id_mapel'
    ];

    public function mataPelajaran()
    {
        return $this->belongsTo(MataPelajaran::class, 'id_mapel', 'id_mapel');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user', 'id_user');
    }
}
