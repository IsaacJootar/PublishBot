<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class UserSettingFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'api_key_encrypted' => null,
            'model' => 'claude-sonnet-4-6',
            'web_search_tool_version' => 'web_search_20250305',
            'max_tokens' => 2000,
            'chapter_count' => 10,
            'words_per_chapter' => 700,
            'pin_count' => 15,
            'default_tone' => null,
            'author_name' => null,
            'quick_topics' => ['Parenting', 'Finance', 'Fitness'],
        ];
    }
}
