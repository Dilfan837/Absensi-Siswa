<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Guru extends Model
{
    use SoftDeletes;
    
    protected $table = 'guru';
    protected $primaryKey = 'id_guru';
    
    protected $fillable = [
        'id_user',
        'guru_id',
        'nuptk',
        'nip',
        'nik',
        'nama',
        'jenis_kelamin',
        'tempat_lahir',
        'tanggal_lahir',
        'agama_id',
        'jenis_ptk_id',
        'status_kepegawaian_id',
        'sekolah_id',
        'email',
        'no_hp',
        'alamat',
        'rt',
        'rw',
        'desa_kelurahan',
        'kecamatan',
        'kode_wilayah',
        'kode_pos',
        'photo',
        'status_aktif',
        'last_synced_at'
    ];

    protected $casts = [
        'status_aktif' => 'boolean',
        'tanggal_lahir' => 'date',
        'last_synced_at' => 'datetime',
    ];

    /**
     * Relasi ke User
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'id_user', 'id_user');
    }
}
