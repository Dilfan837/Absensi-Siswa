<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JadwalSlot extends Model
{
    protected $table = 'jadwal_slots';
    protected $fillable = ['jam_ke', 'jam_mulai', 'jam_selesai', 'hari_berlaku', 'is_istirahat'];

    public function jadwals()
    {
        return $this->hasMany(Jadwal::class, 'jadwal_slot_id');
    }

    /**
     * Scope: hanya slot pelajaran (bukan istirahat)
     */
    public function scopePelajaran($query)
    {
        return $query->where('is_istirahat', false);
    }

    /**
     * Scope: slot untuk hari tertentu
     */
    public function scopeForHari($query, $hari)
    {
        $type = ($hari === 'jumat') ? 'jumat' : 'senin-kamis';
        return $query->where(function ($q) use ($type) {
            $q->where('hari_berlaku', $type)
              ->orWhere('hari_berlaku', 'semua');
        });
    }
}
