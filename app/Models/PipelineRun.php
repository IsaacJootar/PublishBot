<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PipelineRun extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'voice_profile_id',
        'series_id',
        'creation_mode',
        'original_filename',
        'raw_uploaded_content',
        'cleaned_content',
        'cleaning_approved',
        'convert_listing',
        'convert_launch',
        'topic',
        'slug',
        'status',
        'current_stage',
        'validation_result',
        'validation_report',
        'user_confirmed_continue',
        'output_path',
        'started_at',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'current_stage' => 'integer',
            'user_confirmed_continue' => 'boolean',
            'cleaning_approved' => 'boolean',
            'convert_listing' => 'array',
            'convert_launch' => 'array',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
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

    public function stages(): HasMany
    {
        return $this->hasMany(PipelineStage::class, 'run_id')->orderBy('id');
    }

    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class, 'run_id');
    }

    public function isRunning(): bool
    {
        return in_array($this->status, ['pending', 'running', 'paused']);
    }

    public function getOutputDirectory(): string
    {
        return storage_path("outputs/{$this->user_id}/{$this->output_path}");
    }
}
