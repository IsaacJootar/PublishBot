<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Project extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'voice_profile_id',
        'series_id',
        'title',
        'product_type',
        'topic',
        'status',
        'current_step',
        'total_steps',
        'book_format',
        'target_age',
        'target_reader',
    ];

    protected $attributes = [
        'status' => 'active',
        'current_step' => 1,
        'total_steps' => 6,
    ];

    protected function casts(): array
    {
        return [
            'current_step' => 'integer',
            'total_steps' => 'integer',
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

    public function series(): BelongsTo
    {
        return $this->belongsTo(Series::class);
    }

    public function researchResults(): HasMany
    {
        return $this->hasMany(ResearchResult::class);
    }

    public function outlines(): HasMany
    {
        return $this->hasMany(Outline::class)->orderBy('chapter_number');
    }

    public function manuscripts(): HasMany
    {
        return $this->hasMany(Manuscript::class)->orderBy('chapter_number');
    }

    public function illustrationPrompts(): HasMany
    {
        return $this->hasMany(IllustrationPrompt::class)->orderBy('page_number');
    }

    public function kdpListing(): HasOne
    {
        return $this->hasOne(KdpListing::class);
    }

    public function digitalProduct(): HasOne
    {
        return $this->hasOne(DigitalProduct::class);
    }

    public function launchPack(): HasOne
    {
        return $this->hasOne(LaunchPack::class);
    }

    public function advanceStep(): void
    {
        if ($this->current_step < $this->total_steps) {
            $this->increment('current_step');
        }
    }

    public function isStepComplete(int $step): bool
    {
        return $this->current_step > $step;
    }
}
