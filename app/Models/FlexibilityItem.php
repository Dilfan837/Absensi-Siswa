<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FlexibilityItem extends Model
{
    protected $table = 'flexibility_items';
    protected $fillable = [
        'item_name',
        'description',
        'item_type',
        'requires_active_session',
        'point_cost',
        'stock_limit',
        'is_active',
        'created_by',
    ];

    protected $casts = [
        'requires_active_session' => 'boolean',
        'is_active'               => 'boolean',
    ];

    /**
     * Label tipe item yang ramah UI
     */
    public function getItemTypeLabelAttribute(): string
    {
        return match ($this->item_type) {
            'BEBAS_ALPHA'      => 'Bebas Alpha',
            'WFH'              => 'WFH / Kerja dari Rumah',
            'IZIN_MENDADAK'    => 'Izin Mendadak',
            'TOLERANSI_TELAT'  => 'Toleransi Terlambat',
            'CUSTOM'           => 'Lainnya',
            default            => $this->item_type,
        };
    }

    /**
     * Scope: hanya item yang aktif
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by', 'id_user');
    }

    public function userTokens()
    {
        return $this->hasMany(UserToken::class, 'id_item');
    }

    /**
     * Cek apakah siswa masih bisa beli bulan ini (stock_limit)
     */
    public function isAvailableForSiswa(int $id_siswa): bool
    {
        if (!$this->stock_limit) {
            return true; // unlimited
        }
        $boughtThisMonth = UserToken::where('id_siswa', $id_siswa)
            ->where('id_item', $this->id)
            ->whereMonth('purchased_at', now()->month)
            ->whereYear('purchased_at', now()->year)
            ->count();

        return $boughtThisMonth < $this->stock_limit;
    }
}
