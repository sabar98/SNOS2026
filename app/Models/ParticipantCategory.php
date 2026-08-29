<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ParticipantCategory extends Model
{
    protected $fillable = [
        'key',
        'label',
        'is_presenter',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_presenter' => 'boolean',
            'is_active' => 'boolean',
        ];
    }
}
