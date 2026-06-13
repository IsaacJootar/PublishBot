<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DigitalProduct extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'voice_profile_id',
        'product_type',
        'niche',
        'product_title',
        'buyer_description',
        'buyer_problem',
        'brief_options',
        'current_stage',
        'status',
        'research_output',
        'structure_output',
        'content_output',
        'publish_pack',
        'recommended_platform',
        'recommended_price',
        'error_message',
        'progress_note',
        'is_archived',
    ];

    protected function casts(): array
    {
        return [
            'brief_options' => 'array',
            'research_output' => 'array',
            'structure_output' => 'array',
            'publish_pack' => 'array',
            'current_stage' => 'integer',
            'recommended_price' => 'decimal:2',
            'is_archived' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function voiceProfile(): BelongsTo
    {
        return $this->belongsTo(VoiceProfile::class);
    }

    public static function productTypes(): array
    {
        return [
            'prompt_library' => [
                'emoji' => '📦',
                'name' => 'Prompt Library',
                'tagline' => 'Curated AI prompts for a specific niche',
                'price' => '$17 – $47',
                'time' => '~45 mins',
            ],
            'sop_pack' => [
                'emoji' => '📋',
                'name' => 'SOP Pack',
                'tagline' => 'Standard operating procedures for a business',
                'price' => '$97 – $297',
                'time' => '~60 mins',
            ],
            'email_sequence_vault' => [
                'emoji' => '📧',
                'name' => 'Email Sequence Vault',
                'tagline' => 'Complete email libraries for a specific niche',
                'price' => '$47 – $197',
                'time' => '~60 mins',
            ],
        ];
    }

    public function typeMeta(): array
    {
        return self::productTypes()[$this->product_type] ?? [];
    }

    public function stageLabels(): array
    {
        return [
            1 => 'Brief',
            2 => 'Research',
            3 => 'Structure',
            4 => 'Content',
            5 => 'Publish Pack',
        ];
    }
}
