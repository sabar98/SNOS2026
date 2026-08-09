<?php

namespace Database\Factories;

use App\Models\ScheduleSession;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ScheduleSession>
 */
class ScheduleSessionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'session_number' => 'A'.$this->faker->unique()->numberBetween(1, 999),
            'room' => 'Ruang '.$this->faker->numberBetween(100, 399),
            'date' => $this->faker->dateTimeBetween('now', '+2 months')->format('Y-m-d'),
            'start_time' => '09:00',
            'end_time' => '09:30',
        ];
    }
}
