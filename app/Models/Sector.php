<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sector extends Model
{
    protected $fillable = [
        'name', 'slug', 'icon', 'color', 'sort_order',
    ];

    public function roadmaps(): HasMany
    {
        return $this->hasMany(Roadmap::class);
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    public function checkins(): HasMany
    {
        return $this->hasMany(Checkin::class);
    }

    public function streaks(): HasMany
    {
        return $this->hasMany(Streak::class);
    }
}
