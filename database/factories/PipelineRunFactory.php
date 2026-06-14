<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class PipelineRunFactory extends Factory
{
    public function definition(): array
    {
        $topic = $this->faker->words(4, true);

        return [
            'user_id' => User::factory(),
            'voice_profile_id' => null,
            'series_id' => null,
            'topic' => $topic,
            'slug' => Str::slug($topic),
            'status' => 'pending',
            'current_stage' => 0,
            'validation_result' => null,
            'validation_report' => null,
            'user_confirmed_continue' => false,
            'output_path' => Str::slug($topic),
            'started_at' => null,
            'completed_at' => null,
        ];
    }
}
