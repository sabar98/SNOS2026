<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PresentationMaterial extends Model
{
    protected $fillable = [
        'article_id',
        'slide_path',
        'video_path',
        'short_bio',
        'official_photo_path',
        'consent_confirmed_at',
    ];

    protected function casts(): array
    {
        return [
            'consent_confirmed_at' => 'datetime',
        ];
    }

    public function article(): BelongsTo
    {
        return $this->belongsTo(Article::class);
    }
}
