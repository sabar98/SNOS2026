<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RegistrationFee extends Model
{
    protected $fillable = [
        'participant_type',
        'attendance_method',
        'amount',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'integer',
        ];
    }

    /**
     * Looks up the fee for a given participant type + attendance method combo,
     * falling back to 0 when no matching rule has been configured yet.
     */
    public static function amountFor(?string $participantType, ?string $attendanceMethod): int
    {
        return (int) (static::query()
            ->where('participant_type', $participantType)
            ->where('attendance_method', $attendanceMethod)
            ->value('amount') ?? 0);
    }
}
