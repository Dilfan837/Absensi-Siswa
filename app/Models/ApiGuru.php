<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApiGuru extends Model
{
    protected $table = 'api_gurus';
    
    protected $fillable = [
        'guru_id',
        'nama',
        'nuptk',
        'nip',
        'jenis_kelamin',
        'tempat_lahir',
        'tanggal_lahir',
        'nik',
        'email',
        'no_hp',
        'is_activated',
        'id_guru_lokal'
    ];
}
