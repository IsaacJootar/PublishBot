<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Manuscript extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'chapter_number',
        'chapter_title',
        'content',
        'word_count',
        'is_approved',
        'revision_notes',
    ];

    protected function casts(): array
    {
        return [
            'chapter_number' => 'integer',
            'word_count' => 'integer',
            'is_approved' => 'boolean',
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }
}
