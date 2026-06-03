<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KeyResult extends Model
{
    use HasFactory;

    const STATUS_EMPTY = '';
    const STATUS_AT_RISK = 'en-riesgo';
    const STATUS_COMPLETED = 'completado';

    protected $fillable = [
        'objective_id',
        'owner_id',
        'title',
        'description',
        'target',
        'current_value',
        'start_value',
        'unit',
        'status',
        'progress',
        'sums_to_objective',
        'blockers',
        'comments',
    ];

    protected $casts = [
        'objective_id' => 'integer',
        'owner_id' => 'integer',
        'target' => 'float',
        'current_value' => 'float',
        'start_value' => 'float',
        'progress' => 'integer',
        'sums_to_objective' => 'boolean',
    ];

    public function scopeWithAll($query)
    {
        return $query->with(['owner', 'objective']);
    }

    public function objective()
    {
        return $this->belongsTo(Objective::class);
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id')->withTrashed();
    }

    public function logs()
    {
        return $this->hasMany(KeyResultLog::class);
    }
}
