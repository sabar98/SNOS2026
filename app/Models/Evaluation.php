<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Evaluation extends Model
{
    protected $fillable = [
        'event_registration_id',
        'speaker_rating',
        'committee_rating',
        'material_quality_rating',
        'facility_rating',
        'zoom_rating',
        'feedback',
    ];

    public function eventRegistration(): BelongsTo
    {
        return $this->belongsTo(EventRegistration::class);
    }
}
