<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PointSetting extends Model
{
    protected $table = 'point_settings';
    protected $fillable = ['key', 'value', 'label', 'updated_by'];

    /**
     * Ambil nilai setting berdasarkan key.
     * Contoh: PointSetting::get('poin_hadir') => 2
     */
    public static function getValue(string $key, int $default = 0): int
    {
        $setting = static::where('key', $key)->first();
        return $setting ? (int) $setting->value : $default;
    }
}
