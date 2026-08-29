<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ParticipantCategory extends Model
{
    public const GOLONGAN_OPTIONS = ['umum', 'dosen', 'mahasiswa'];

    protected $fillable = [
        'key',
        'label',
        'golongan',
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
