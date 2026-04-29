<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    public function scopeWithAll($query)
    {
        return $query;
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function admins()
    {
        return $this->hasMany(User::class)->where('role', User::ROLE_ADMIN);
    }

    public function departments()
    {
        return $this->hasMany(Department::class);
    }

    public function remis()
    {
        return $this->hasMany(Remi::class);
    }

    public function objectives()
    {
        return $this->hasMany(Objective::class);
    }
}
