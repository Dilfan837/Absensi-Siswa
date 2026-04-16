<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PointLedger extends Model
{
    protected $table = 'point_ledgers';
    protected $fillable = [
        'id_siswa',
        'transaction_type',
        'amount',
        'current_balance',
        'description',
        'id_absensi',
        'id_guru',
    ];

    public function siswa()
    {
        return $this->belongsTo(Siswa::class, 'id_siswa', 'id_siswa');
    }

    public function absensi()
    {
        return $this->belongsTo(Absensi::class, 'id_absensi', 'id_absensi');
    }

    public function guru()
    {
        return $this->belongsTo(Guru::class, 'id_guru', 'id_guru');
    }
}
