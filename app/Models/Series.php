<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Series extends Model
{
    use HasFactory;

    protected $table = 'series';

    protected $fillable = [
        'user_id',
        'name',
        'emoji',
        'color',
        'format',
        'description',
        'characters',
        'art_style',
        'writing_tone',
        'recurring_themes',
        'never_do',
    ];

    protected function casts(): array
    {
        return [
            'characters' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function runs(): HasMany
    {
        return $this->hasMany(PipelineRun::class);
    }

    /** Build a system-prompt suffix that injects character bible + style guide. */
    public function promptContext(): string
    {
        $lines = ["\n\n--- SERIES CONTEXT: {$this->name} ---"];

        if ($this->description) {
            $lines[] = "Series: {$this->description}";
        }

        $chars = $this->characters ?? [];
        if (! empty($chars)) {
            $lines[] = "\nRecurring characters (use these consistently — do NOT rename or redescribe them):";
            foreach ($chars as $c) {
                $name = trim((string) ($c['name'] ?? ''));
                if (! $name) {
                    continue;
                }
                $bits = [];
                if (! empty($c['age_appearance'])) {
                    $bits[] = $c['age_appearance'];
                }
                if (! empty($c['personality'])) {
                    $bits[] = $c['personality'];
                }
                if (! empty($c['key_detail'])) {
                    $bits[] = $c['key_detail'];
                }
                $lines[] = '- '.$name.($bits ? ': '.implode(' · ', $bits) : '');
            }
        }

        if ($this->art_style) {
            $lines[] = "\nArt style: {$this->art_style}";
        }
        if ($this->writing_tone) {
            $lines[] = "Writing tone: {$this->writing_tone}";
        }
        if ($this->recurring_themes) {
            $lines[] = "Recurring themes: {$this->recurring_themes}";
        }
        if ($this->never_do) {
            $lines[] = "Things this series never does: {$this->never_do}";
        }

        $lines[] = "--- END SERIES CONTEXT ---\n";
        $lines[] = 'Maintain continuity with the series. Match the established tone, characters, and style.';

        return implode("\n", $lines);
    }
}
