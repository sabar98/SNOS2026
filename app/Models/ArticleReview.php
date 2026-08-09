<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ArticleReview extends Model
{
    protected $fillable = [
        'article_reviewer_id',
        'theme_suitability_score',
        'novelty_score',
        'methodology_score',
        'results_discussion_score',
        'reference_quality_score',
        'language_grammar_score',
        'comments',
        'recommendation',
        'reviewed_at',
    ];

    protected function casts(): array
    {
        return [
            'reviewed_at' => 'datetime',
        ];
    }

    public function articleReviewer(): BelongsTo
    {
        return $this->belongsTo(ArticleReviewer::class);
    }
}
