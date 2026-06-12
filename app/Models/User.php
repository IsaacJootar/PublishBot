<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'author_name',
        'pen_name',
        'anthropic_api_key',
        'openai_api_key',
        'onboarding_complete',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'anthropic_api_key' => 'encrypted',
            'openai_api_key' => 'encrypted',
            'onboarding_complete' => 'boolean',
        ];
    }

    public function voiceProfiles(): HasMany
    {
        return $this->hasMany(VoiceProfile::class);
    }

    public function defaultVoiceProfile(): HasOne
    {
        return $this->hasOne(VoiceProfile::class)->where('is_default', true);
    }

    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
    }

    public function series(): HasMany
    {
        return $this->hasMany(Series::class);
    }
}
