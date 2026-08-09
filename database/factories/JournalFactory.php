<?php

namespace Database\Factories;

use App\Models\Journal;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Journal>
 */
class JournalFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->company().' Journal',
            'type' => 'jurnal',
            'publisher' => $this->faker->company(),
            'publication_fee' => 0,
            'is_active' => true,
        ];
    }
}
