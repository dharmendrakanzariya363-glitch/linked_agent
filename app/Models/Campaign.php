<?php

namespace App\Models;

use App\Enums\CampaignStatus;
use App\Enums\ContentType;
use Database\Factories\CampaignFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $user_id
 * @property int $linkedin_account_id
 * @property string $name
 * @property string $timezone
 * @property string $daily_post_time
 * @property Carbon $start_date
 * @property Carbon|null $end_date
 * @property ContentType $content_type
 * @property CampaignStatus $status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable([
    'user_id',
    'linkedin_account_id',
    'name',
    'timezone',
    'daily_post_time',
    'start_date',
    'end_date',
    'content_type',
    'status',
])]
class Campaign extends Model
{
    /** @use HasFactory<CampaignFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'content_type' => ContentType::class,
            'status' => CampaignStatus::class,
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
     * @return BelongsTo<LinkedInAccount, $this>
     */
    public function linkedinAccount(): BelongsTo
    {
        return $this->belongsTo(LinkedInAccount::class);
    }

    /**
     * @return HasMany<CampaignTopic, $this>
     */
    public function topics(): HasMany
    {
        return $this->hasMany(CampaignTopic::class)->orderBy('sort_order');
    }

    /**
     * @return HasMany<CampaignTopic, $this>
     */
    public function activeTopics(): HasMany
    {
        return $this->topics()->where('is_active', true);
    }

    /**
     * @return HasMany<Post, $this>
     */
    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }

    public function requiresImage(): bool
    {
        return $this->content_type->includesImage();
    }

    public function isActive(): bool
    {
        return $this->status === CampaignStatus::Active;
    }

    public function localDate(?Carbon $moment = null): string
    {
        return ($moment ?? now())->timezone($this->timezone)->toDateString();
    }

    public function localToday(): Carbon
    {
        return now($this->timezone)->startOfDay();
    }
}
