<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ResearchResult extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'topic',
        'title_angle',
        'book_format',
        'buyer_intent_score',
        'competition_level',
        'reason',
        'is_selected',
    ];

    protected function casts(): array
    {
        return [
            'buyer_intent_score' => 'integer',
            'is_selected' => 'boolean',
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }
}
