<?php

namespace App\Models;

use Database\Factories\LinkedInAccountFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $user_id
 * @property string $linkedin_id
 * @property string $name
 * @property string|null $email
 * @property string|null $headline
 * @property string|null $profile_url
 * @property string|null $avatar_url
 * @property string|null $access_token
 * @property string|null $refresh_token
 * @property Carbon|null $token_expires_at
 * @property list<string>|null $scopes
 * @property Carbon|null $disconnected_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable([
    'user_id',
    'linkedin_id',
    'name',
    'email',
    'headline',
    'profile_url',
    'avatar_url',
    'access_token',
    'refresh_token',
    'token_expires_at',
    'scopes',
    'disconnected_at',
])]
#[Hidden(['access_token', 'refresh_token'])]
class LinkedInAccount extends Model
{
    /** @use HasFactory<LinkedInAccountFactory> */
    use HasFactory;

    protected $table = 'linkedin_accounts';

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'access_token' => 'encrypted',
            'refresh_token' => 'encrypted',
            'token_expires_at' => 'datetime',
            'scopes' => 'array',
            'disconnected_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return HasMany<Campaign, $this>
     */
    public function campaigns(): HasMany
    {
        return $this->hasMany(Campaign::class);
    }

    /**
     * @param  Builder<LinkedInAccount>  $query
     * @return Builder<LinkedInAccount>
     */
    public function scopeConnected(Builder $query): Builder
    {
        return $query->whereNull('disconnected_at')->whereNotNull('access_token');
    }

    public function isConnected(): bool
    {
        return $this->disconnected_at === null && filled($this->access_token);
    }

    public function tokenIsExpired(): bool
    {
        return $this->token_expires_at !== null && $this->token_expires_at->lte(now()->addMinutes(2));
    }

    public function personUrn(): string
    {
        return 'urn:li:person:'.$this->linkedin_id;
    }
}
