<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class SeriesFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'name' => $this->faker->words(3, true),
            'emoji' => '📚',
            'color' => '#6C3CE1',
            'format' => 'Non-fiction',
            'description' => $this->faker->sentence(),
            'characters' => [],
            'art_style' => null,
            'writing_tone' => null,
            'recurring_themes' => null,
            'never_do' => null,
        ];
    }
}
