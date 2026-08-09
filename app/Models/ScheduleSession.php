<?php

namespace App\Models;

use Database\Factories\ScheduleSessionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ScheduleSession extends Model
{
    /** @use HasFactory<ScheduleSessionFactory> */
    use HasFactory;

    protected $fillable = [
        'session_number',
        'room',
        'moderator_id',
        'date',
        'start_time',
        'end_time',
        'zoom_link',
        'zoom_meeting_id',
        'zoom_password',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
        ];
    }

    public function moderator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'moderator_id');
    }

    public function presentationSlots(): HasMany
    {
        return $this->hasMany(PresentationSlot::class)->orderBy('order');
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }
}
