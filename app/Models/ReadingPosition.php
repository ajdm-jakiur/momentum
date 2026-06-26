<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReadingPosition extends Model
{
    protected $fillable = ['user_id', 'book_id', 'current_page', 'last_read_at'];

    protected $casts = ['last_read_at' => 'datetime'];

    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
