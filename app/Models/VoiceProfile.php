<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VoiceProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'emoji',
        'color',
        'description',
        'raw_content',
        'extracted_style',
        'word_count',
        'is_default',
        'status',
        'error_message',
    ];

    protected function casts(): array
    {
        return [
            'is_default' => 'boolean',
            'word_count' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function qualityLabel(): string
    {
        return match (true) {
            $this->word_count < 500 => 'Needs more content',
            $this->word_count < 1000 => 'Getting there',
            $this->word_count < 2000 => 'Good',
            $this->word_count < 5000 => 'Strong',
            default => 'Excellent',
        };
    }

    public function qualityTone(): string
    {
        return match (true) {
            $this->word_count < 500 => 'amber',
            $this->word_count < 2000 => 'blue',
            default => 'green',
        };
    }

    public static function suggestedDomains(): array
    {
        return [
            ['emoji' => '✍️', 'name' => 'General Writing', 'description' => 'Your default voice for any topic', 'color' => '#6C3CE1'],
            ['emoji' => '📱', 'name' => 'Social Media & Algorithms', 'description' => 'Platform guides, creator content', 'color' => '#EC4899'],
            ['emoji' => '💼', 'name' => 'Business & Entrepreneurship', 'description' => 'Strategy, pricing, solopreneur content', 'color' => '#0EA5E9'],
            ['emoji' => '🧒', 'name' => "Children's Content", 'description' => 'Stories and educational books', 'color' => '#F59E0B'],
            ['emoji' => '🙏', 'name' => 'Faith & Personal Dev', 'description' => 'Devotionals, growth, mindset', 'color' => '#8B5CF6'],
            ['emoji' => '💻', 'name' => 'Tech & Digital Tools', 'description' => 'AI guides, tool breakdowns', 'color' => '#10B981'],
            ['emoji' => '🌿', 'name' => 'Health & Wellness', 'description' => 'Habits, fitness, mental health', 'color' => '#22C55E'],
            ['emoji' => '💰', 'name' => 'Finance & Money', 'description' => 'Budgeting, investing, income', 'color' => '#EAB308'],
        ];
    }
}
