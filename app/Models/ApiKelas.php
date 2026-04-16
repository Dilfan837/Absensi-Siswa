<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApiKelas extends Model
{
    protected $table = 'api_kelas';
    
    protected $fillable = [
        'kelas_id',
        'nama',
        'jurusan_api',
        'is_activated',
        'id_kelas_lokal'
    ];
}
