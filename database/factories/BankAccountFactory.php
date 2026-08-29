<?php

namespace Database\Factories;

use App\Models\BankAccount;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BankAccount>
 */
class BankAccountFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'bank_name' => fake()->randomElement(['BCA', 'BNI', 'BRI', 'Mandiri', 'BSI']),
            'account_number' => fake()->numerify('##########'),
            'account_holder' => 'Panitia '.fake()->company(),
            'is_active' => true,
        ];
    }
}
