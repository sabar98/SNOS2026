<?php

namespace App\Models;

use Database\Factories\ArticleFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Article extends Model
{
    /** @use HasFactory<ArticleFactory> */
    use HasFactory;

    protected $fillable = [
        'event_registration_id',
        'journal_id',
        'article_number',
        'title',
        'abstract',
        'keywords',
        'field',
        'file_path',
        'statement_letter_path',
        'similarity_score',
        'admin_notes',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'similarity_score' => 'decimal:2',
        ];
    }

    public function eventRegistration(): BelongsTo
    {
        return $this->belongsTo(EventRegistration::class);
    }

    public function journal(): BelongsTo
    {
        return $this->belongsTo(Journal::class);
    }

    public function authors(): HasMany
    {
        return $this->hasMany(ArticleAuthor::class)->orderBy('order');
    }

    public function reviewerAssignments(): HasMany
    {
        return $this->hasMany(ArticleReviewer::class);
    }

    public function reviews(): HasManyThrough
    {
        return $this->hasManyThrough(ArticleReview::class, ArticleReviewer::class, 'article_id', 'article_reviewer_id');
    }

    public function revisions(): HasMany
    {
        return $this->hasMany(ArticleRevision::class);
    }

    public function letterOfAcceptance(): HasOne
    {
        return $this->hasOne(LetterOfAcceptance::class);
    }

    public function presentationSlots(): HasMany
    {
        return $this->hasMany(PresentationSlot::class);
    }

    public function presentationMaterial(): HasOne
    {
        return $this->hasOne(PresentationMaterial::class);
    }

    public function publication(): HasOne
    {
        return $this->hasOne(Publication::class);
    }

    public function payments(): MorphMany
    {
        return $this->morphMany(Payment::class, 'payable');
    }
}
