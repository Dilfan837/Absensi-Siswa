<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Assessment extends Model
{
    protected $fillable = [
        'evaluator_id',
        'evaluatee_id',
        'context_type',
        'context_id',
        'assessment_date',
        'period',
        'general_notes',
    ];

    public function evaluator()
    {
        return $this->belongsTo(User::class, 'evaluator_id', 'id_user');
    }

    public function evaluatee()
    {
        return $this->belongsTo(User::class, 'evaluatee_id', 'id_user');
    }

    public function details()
    {
        return $this->hasMany(AssessmentDetail::class, 'assessment_id');
    }
}
