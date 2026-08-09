<?php

namespace App\Models;

use Database\Factories\PresentationSlotFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PresentationSlot extends Model
{
    /** @use HasFactory<PresentationSlotFactory> */
    use HasFactory;

    protected $fillable = [
        'schedule_session_id',
        'article_id',
        'order',
        'status',
        'execution_notes',
    ];

    public function scheduleSession(): BelongsTo
    {
        return $this->belongsTo(ScheduleSession::class);
    }

    public function article(): BelongsTo
    {
        return $this->belongsTo(Article::class);
    }

    public function assessments(): HasMany
    {
        return $this->hasMany(PresentationAssessment::class);
    }
}
