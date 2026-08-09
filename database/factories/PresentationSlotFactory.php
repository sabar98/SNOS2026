<?php

namespace Database\Factories;

use App\Models\Article;
use App\Models\PresentationSlot;
use App\Models\ScheduleSession;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PresentationSlot>
 */
class PresentationSlotFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'schedule_session_id' => ScheduleSession::factory(),
            'article_id' => Article::factory(),
            'order' => 1,
            'status' => 'dijadwalkan',
        ];
    }
}
