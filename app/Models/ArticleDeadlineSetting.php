<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ArticleDeadlineSetting extends Model
{
    protected $fillable = [
        'article_submission_deadline',
        'presentation_material_deadline',
    ];

    protected function casts(): array
    {
        return [
            'article_submission_deadline' => 'datetime',
            'presentation_material_deadline' => 'datetime',
        ];
    }

    /**
     * The deadlines have exactly one editable record, seeded from the config
     * defaults the first time anything asks for it — so moving this from
     * config/seminar.php to the database never changes the deadline that was
     * already in effect.
     */
    public static function current(): self
    {
        return static::firstOrCreate([], [
            'article_submission_deadline' => config('seminar.article_submission_deadline'),
            'presentation_material_deadline' => config('seminar.presentation_material_deadline'),
        ]);
    }
}
