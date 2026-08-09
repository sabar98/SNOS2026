<?php

namespace App\Models;

use Database\Factories\JournalFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Journal extends Model
{
    /** @use HasFactory<JournalFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
        'publisher',
        'issn',
        'website_url',
        'publication_fee',
        'description',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'publication_fee' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function articles(): HasMany
    {
        return $this->hasMany(Article::class);
    }

    public function publications(): HasMany
    {
        return $this->hasMany(Publication::class);
    }
}
