<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class DigitalProductFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'voice_profile_id' => null,
            'product_type' => 'prompt_library',
            'niche' => $this->faker->words(3, true),
            'product_title' => null,
            'buyer_description' => $this->faker->sentence(),
            'buyer_problem' => $this->faker->sentence(),
            'brief_options' => ['prompt_count' => 30],
            'current_stage' => 1,
            'status' => 'draft',
            'research_output' => null,
            'structure_output' => null,
            'content_output' => null,
            'publish_pack' => null,
            'recommended_platform' => null,
            'recommended_price' => null,
            'error_message' => null,
            'progress_note' => null,
            'is_archived' => false,
        ];
    }
}
