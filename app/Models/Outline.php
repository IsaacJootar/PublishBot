<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Outline extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'chapter_number',
        'chapter_title',
        'chapter_summary',
        'page_count_est',
        'learning_obj',
        'illustration_note',
        'is_approved',
    ];

    protected function casts(): array
    {
        return [
            'chapter_number' => 'integer',
            'page_count_est' => 'integer',
            'is_approved' => 'boolean',
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }
}
