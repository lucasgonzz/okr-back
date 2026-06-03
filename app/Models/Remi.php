<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Remi extends Model
{
    use HasFactory;

    protected $table = 'remis';

    protected $fillable = [
        'company_id',
        'name',
        'description',
        'responsible_user_id',
        'responsible_department_id',
        'progreso_manual',
    ];

    protected $casts = [
        'company_id' => 'integer',
        'responsible_user_id' => 'integer',
        'responsible_department_id' => 'integer',
        'progreso_manual' => 'integer',
    ];

    public function scopeWithAll($query)
    {
        return $query->with(['responsibleUser', 'responsibleDepartment', 'quarters']);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function responsibleUser()
    {
        return $this->belongsTo(User::class, 'responsible_user_id')->withTrashed();
    }

    public function responsibleDepartment()
    {
        return $this->belongsTo(Department::class, 'responsible_department_id');
    }

    public function objectives()
    {
        return $this->hasMany(Objective::class);
    }

    public function quarters()
    {
        return $this->belongsToMany(Quarter::class, 'quarter_remi');
    }
}
