<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'name',
        'slug',
        'color',
    ];

    protected $casts = [
        'company_id' => 'integer',
    ];

    public function scopeWithAll($query)
    {
        return $query->with(['company']);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function objectives()
    {
        return $this->hasMany(Objective::class);
    }
}
