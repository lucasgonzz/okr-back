<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KeyResultLog extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'key_result_id',
        'user_id',
        'changes',
        'created_at',
    ];

    protected $casts = [
        'key_result_id' => 'integer',
        'user_id' => 'integer',
        'changes' => 'array',
        'created_at' => 'datetime',
    ];

    public function scopeWithAll($query)
    {
        return $query->with(['user', 'keyResult.objective']);
    }

    public function keyResult()
    {
        return $this->belongsTo(KeyResult::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
