<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BlockItem extends Model
{
    protected $fillable = [
        'block_id', 'kind', 'title', 'body', 'meta',
        'sort_order', 'is_required', 'is_done', 'done_at',
    ];

    protected $casts = [
        'meta' => 'array',
        'is_required' => 'boolean',
        'is_done' => 'boolean',
        'done_at' => 'datetime',
    ];

    public function block(): BelongsTo
    {
        return $this->belongsTo(Block::class);
    }
}
