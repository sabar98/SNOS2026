<?php

namespace Database\Factories;

use App\Models\EventRegistration;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<EventRegistration>
 */
class EventRegistrationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'registration_number' => 'SNOS2026-'.Str::upper(Str::random(8)),
            'user_id' => User::factory(),
            'participant_type' => 'peserta_umum',
            'attendance_method' => 'luring',
            'institution' => $this->faker->company(),
            'status' => 'draft',
        ];
    }
}
