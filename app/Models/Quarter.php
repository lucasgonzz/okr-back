<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Quarter extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'name',
        'year',
        'position',
    ];

    protected $casts = [
        'company_id' => 'integer',
        'year' => 'integer',
        'position' => 'integer',
    ];

    public function scopeWithAll($query)
    {
        return $query->with(['company']);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function objectives()
    {
        return $this->hasMany(Objective::class);
    }
}
