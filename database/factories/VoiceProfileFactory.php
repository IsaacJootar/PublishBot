<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class VoiceProfileFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'name' => $this->faker->words(3, true),
            'emoji' => '✍️',
            'color' => '#6C3CE1',
            'description' => $this->faker->sentence(),
            'raw_content' => null,
            'extracted_style' => null,
            'word_count' => 0,
            'is_default' => false,
            'status' => 'draft',
            'error_message' => null,
        ];
    }
}
