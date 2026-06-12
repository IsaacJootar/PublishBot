<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LaunchPack extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'social_post_linkedin',
        'social_post_twitter',
        'social_post_instagram',
        'social_post_pinterest',
        'social_post_whatsapp',
        'email_1_subject',
        'email_1_body',
        'email_2_subject',
        'email_2_body',
        'email_3_subject',
        'email_3_body',
        'review_request',
        'is_approved',
    ];

    protected function casts(): array
    {
        return [
            'is_approved' => 'boolean',
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }
}
