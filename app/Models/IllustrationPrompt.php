<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IllustrationPrompt extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'page_number',
        'page_text',
        'prompt',
        'character_desc',
        'style_desc',
        'is_completed',
    ];

    protected function casts(): array
    {
        return [
            'page_number' => 'integer',
            'is_completed' => 'boolean',
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }
}
