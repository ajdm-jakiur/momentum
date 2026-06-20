<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Roadmap extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'sector_id', 'title', 'description', 'color', 'total_weeks',
        'source', 'imported_json', 'sort_order',
        'progress_percent', 'is_complete', 'completed_at',
    ];

    protected $casts = [
        'is_complete' => 'boolean',
        'completed_at' => 'datetime',
    ];

    public function sector(): BelongsTo
    {
        return $this->belongsTo(Sector::class);
    }

    public function phases(): HasMany
    {
        return $this->hasMany(Phase::class)->orderBy('sort_order');
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    public function checkins(): HasMany
    {
        return $this->hasMany(Checkin::class);
    }
}
