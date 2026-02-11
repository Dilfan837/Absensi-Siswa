<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApiSyncLog extends Model
{
    protected $table = 'api_sync_logs';
    
    protected $fillable = [
        'api_type',
        'records_fetched',
        'records_created',
        'records_updated',
        'records_failed',
        'error_details',
        'status',
        'started_at',
        'completed_at',
        'duration_seconds',
        'triggered_by_user_id'
    ];

    protected $casts = [
        'error_details' => 'array',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    /**
     * Relasi ke User yang trigger sync
     */
    public function triggeredBy()
    {
        return $this->belongsTo(User::class, 'triggered_by_user_id', 'id_user');
    }
}
