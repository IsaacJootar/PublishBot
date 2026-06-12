<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KdpListing extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'book_title',
        'subtitle',
        'description',
        'keywords',
        'primary_category',
        'secondary_category',
        'author_bio',
        'price_recommendation',
        'is_approved',
    ];

    protected function casts(): array
    {
        return [
            'keywords' => 'array',
            'is_approved' => 'boolean',
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }
}
