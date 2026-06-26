<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Storage;

class Book extends Model
{
    protected $casts = [
        'user_id'    => 'integer',
        'sector_id'  => 'integer',
        'file_size'  => 'integer',
        'page_count' => 'integer',
    ];

    protected $fillable = [
        'user_id', 'sector_id', 'title', 'author', 'r2_key',
        'mime_type', 'file_size', 'page_count', 'cover_color', 'description',
        'cover_image', 'cover_mime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function sector(): BelongsTo
    {
        return $this->belongsTo(Sector::class);
    }

    public function readingPosition(): HasOne
    {
        return $this->hasOne(ReadingPosition::class);
    }

    public function fileSizeForHumans(): string
    {
        $bytes = $this->file_size;
        if ($bytes >= 1_048_576) return round($bytes / 1_048_576, 1).' MB';
        if ($bytes >= 1_024) return round($bytes / 1_024, 1).' KB';
        return $bytes.' B';
    }

    public function temporaryUrl(int $minutesTtl = 60): string
    {
        return Storage::disk('r2')->temporaryUrl($this->r2_key, now()->addMinutes($minutesTtl));
    }
}
