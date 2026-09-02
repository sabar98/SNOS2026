<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LandingSetting extends Model
{
    protected $fillable = [
        'name',
        'site_name',
        'site_logo_path',
        'organizer',
        'contact',
        'theme',
        'date_range',
        'location',
        'scope',
        'speakers',
        'timeline',
        'leader_message',
        'partners',
    ];

    protected function casts(): array
    {
        return [
            'contact' => 'array',
            'scope' => 'array',
            'speakers' => 'array',
            'timeline' => 'array',
            'leader_message' => 'array',
            'partners' => 'array',
        ];
    }

    /**
     * The landing page has exactly one editable record. Seed it from the
     * config defaults the first time anything asks for it.
     */
    public static function current(): self
    {
        return static::firstOrCreate([], [
            'name' => config('seminar.name'),
            'site_name' => 'SNOS 2026',
            'site_logo_path' => null,
            'organizer' => config('seminar.organizer'),
            'contact' => config('seminar.contact'),
            'theme' => config('seminar.theme'),
            'date_range' => config('seminar.date_range'),
            'location' => config('seminar.location'),
            'scope' => config('seminar.scope'),
            'speakers' => collect(config('seminar.speakers'))
                ->map(fn (array $speaker) => [...$speaker, 'topic' => null, 'photo_path' => null])
                ->all(),
            'timeline' => config('seminar.timeline'),
            'leader_message' => [
                'name' => config('seminar.certificate_signer.name'),
                'title' => config('seminar.certificate_signer.title'),
                'message' => 'Selamat datang di '.config('seminar.name').'. Semoga kegiatan ini menjadi ruang berbagi gagasan dan mempererat kolaborasi ilmiah demi kemajuan bersama.',
                'photo_path' => null,
            ],
            'partners' => [
                ['name' => 'Universitas Contoh', 'logo_path' => null],
                ['name' => 'Pemerintah Kota Contoh', 'logo_path' => null],
                ['name' => 'Asosiasi Ilmuwan Nasional', 'logo_path' => null],
                ['name' => 'Lembaga Riset Terapan', 'logo_path' => null],
            ],
        ]);
    }
}
