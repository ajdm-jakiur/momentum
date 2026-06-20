<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Block extends Model
{
    protected $fillable = [
        'phase_id', 'title', 'weeks_label', 'icon', 'pattern_text', 'sort_order',
        'required_total', 'required_done', 'is_complete', 'completed_at',
    ];

    protected $casts = [
        'is_complete' => 'boolean',
        'completed_at' => 'datetime',
    ];

    public function phase(): BelongsTo
    {
        return $this->belongsTo(Phase::class);
    }

    public function resources(): HasMany
    {
        return $this->hasMany(BlockResource::class)->orderBy('sort_order');
    }

    public function items(): HasMany
    {
        return $this->hasMany(BlockItem::class)->orderBy('sort_order');
    }

    public function dailyNotes(): HasMany
    {
        return $this->hasMany(BlockDailyNote::class)->orderBy('sort_order');
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    public function checkins(): HasMany
    {
        return $this->hasMany(Checkin::class);
    }

    /** "still needed" chip list — required, not-yet-done resources + items. */
    public function missingRequired(): \Illuminate\Support\Collection
    {
        $missingResources = $this->resources->where('is_required', true)->where('is_done', false)
            ->map(fn ($r) => "{$r->name} (book)");

        $missingItems = $this->items->where('is_required', true)->where('is_done', false)
            ->map(fn ($i) => "{$i->title} ({$i->kind})");

        return $missingResources->merge($missingItems)->values();
    }
}
