<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'reward_points',
        'lottery_id',
    ];

    public function users()
    {
        return $this->belongsToMany(User::class, 'user_tasks')
            ->withPivot(['status', 'proof', 'verified_at'])
            ->withTimestamps();
    }

    public function lottery()
    {
        return $this->belongsTo(Lottery::class);
    }
}
