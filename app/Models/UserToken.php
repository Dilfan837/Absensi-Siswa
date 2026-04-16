<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserToken extends Model
{
    protected $table = 'user_tokens';
    protected $fillable = [
        'id_siswa',
        'id_item',
        'status',
        'id_absensi_used',
        'used_at',
        'purchased_at',
    ];

    protected $casts = [
        'used_at'      => 'datetime',
        'purchased_at' => 'datetime',
    ];

    public function siswa()
    {
        return $this->belongsTo(Siswa::class, 'id_siswa', 'id_siswa');
    }

    public function item()
    {
        return $this->belongsTo(FlexibilityItem::class, 'id_item');
    }

    public function absensiUsed()
    {
        return $this->belongsTo(Absensi::class, 'id_absensi_used', 'id_absensi');
    }

    public function scopeAvailable($query)
    {
        return $query->where('status', 'AVAILABLE');
    }
}
