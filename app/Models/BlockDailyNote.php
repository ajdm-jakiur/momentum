<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BlockDailyNote extends Model
{
    protected $fillable = ['block_id', 'body', 'sort_order'];

    public function block(): BelongsTo
    {
        return $this->belongsTo(Block::class);
    }
}
