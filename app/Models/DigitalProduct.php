<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DigitalProduct extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'product_type',
        'platform',
        'price_usd',
        'sections',
        'sales_page_title',
        'sales_page_body',
        'tagline',
        'is_approved',
    ];

    protected function casts(): array
    {
        return [
            'sections' => 'array',
            'price_usd' => 'decimal:2',
            'is_approved' => 'boolean',
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }
}
