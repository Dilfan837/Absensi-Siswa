<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SekolahSetting extends Model
{
    protected $fillable = [
        'is_geofence_active'
    ];

    protected $casts = [
        'is_geofence_active' => 'boolean',
    ];
}
