<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'api_key_encrypted',
        'model',
        'web_search_tool_version',
        'max_tokens',
        'chapter_count',
        'words_per_chapter',
        'pin_count',
        'default_tone',
        'author_name',
        'quick_topics',
    ];

    protected function casts(): array
    {
        return [
            'api_key_encrypted' => 'encrypted',
            'max_tokens' => 'integer',
            'chapter_count' => 'integer',
            'words_per_chapter' => 'integer',
            'pin_count' => 'integer',
            'quick_topics' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function defaults(): array
    {
        return [
            'model' => 'claude-sonnet-4-6',
            'web_search_tool_version' => 'web_search_20250305',
            'max_tokens' => 2000,
            'chapter_count' => 8,
            'words_per_chapter' => 500,
            'pin_count' => 15,
            'quick_topics' => ['Parenting', 'Finance', 'Fitness', 'Mindset', 'Productivity', 'Relationships', 'Cooking', 'Kids Education', 'Health', 'Business'],
        ];
    }
}
