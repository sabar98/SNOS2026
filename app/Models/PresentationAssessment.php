<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PresentationAssessment extends Model
{
    protected $fillable = [
        'presentation_slot_id',
        'assessed_by',
        'mastery_score',
        'presentation_quality_score',
        'timeliness_score',
        'qa_score',
        'content_alignment_score',
        'notes',
    ];

    public function presentationSlot(): BelongsTo
    {
        return $this->belongsTo(PresentationSlot::class);
    }

    public function assessor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assessed_by');
    }
}
