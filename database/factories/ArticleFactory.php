<?php

namespace Database\Factories;

use App\Models\Article;
use App\Models\EventRegistration;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Article>
 */
class ArticleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'event_registration_id' => EventRegistration::factory(),
            'title' => $this->faker->sentence(6),
            'abstract' => $this->faker->paragraph(),
            'keywords' => 'kata kunci, contoh, penelitian',
            'field' => 'Teknologi Informasi',
            'file_path' => 'articles/dummy.pdf',
            'statement_letter_path' => 'article-statements/dummy.pdf',
            'status' => 'draft',
        ];
    }
}
