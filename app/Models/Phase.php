<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Phase extends Model
{
    protected $fillable = [
        'roadmap_id', 'title', 'duration_label', 'description', 'milestone',
        'milestone_confirmed', 'color', 'sort_order',
        'progress_percent', 'is_complete', 'completed_at',
    ];

    protected $casts = [
        'milestone_confirmed' => 'boolean',
        'is_complete' => 'boolean',
        'completed_at' => 'datetime',
    ];

    public function roadmap(): BelongsTo
    {
        return $this->belongsTo(Roadmap::class);
    }

    public function blocks(): HasMany
    {
        return $this->hasMany(Block::class)->orderBy('sort_order');
    }
}
