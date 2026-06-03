<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Objective extends Model
{
    use HasFactory;

    const CALC_AUTOMATIC = 'automatic';
    const CALC_MANUAL = 'manual';

    const STATUS_EMPTY = '';
    const STATUS_AT_RISK = 'en-riesgo';
    const STATUS_COMPLETED = 'completado';

    protected $fillable = [
        'company_id',
        'department_id',
        'owner_id',
        'remi_id',
        'quarter_id',
        'title',
        'description',
        'quarter',
        'calculation_mode',
        'manual_progress',
        'manual_status',
        'progress',
        'status',
    ];

    protected $casts = [
        'company_id' => 'integer',
        'department_id' => 'integer',
        'owner_id' => 'integer',
        'remi_id' => 'integer',
        'quarter_id' => 'integer',
        'manual_progress' => 'integer',
        'progress' => 'integer',
    ];

    public function scopeWithAll($query)
    {
        return $query->with([
            'department',
            'owner',
            'quarterModel',
            'remi.responsibleUser',
            'remi.responsibleDepartment',
            'keyResults.owner',
        ]);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id')->withTrashed();
    }

    public function remi()
    {
        return $this->belongsTo(Remi::class);
    }

    public function quarterModel()
    {
        return $this->belongsTo(Quarter::class, 'quarter_id');
    }

    public function keyResults()
    {
        return $this->hasMany(KeyResult::class);
    }
}
