<?php

namespace App\Models;

use App\Enums\PostStatus;
use Database\Factories\PostFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $user_id
 * @property int $campaign_id
 * @property int|null $campaign_topic_id
 * @property int $linkedin_account_id
 * @property int|null $current_version_id
 * @property PostStatus $status
 * @property Carbon $scheduled_for
 * @property Carbon|null $generated_at
 * @property Carbon|null $approved_at
 * @property Carbon|null $published_at
 * @property string|null $linkedin_post_id
 * @property string|null $published_url
 * @property string|null $last_error
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable([
    'user_id',
    'campaign_id',
    'campaign_topic_id',
    'linkedin_account_id',
    'current_version_id',
    'status',
    'scheduled_for',
    'generated_at',
    'approved_at',
    'published_at',
    'linkedin_post_id',
    'published_url',
    'last_error',
])]
class Post extends Model
{
    /** @use HasFactory<PostFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => PostStatus::class,
            'scheduled_for' => 'date',
            'generated_at' => 'datetime',
            'approved_at' => 'datetime',
            'published_at' => 'datetime',
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
     * @return BelongsTo<Campaign, $this>
     */
    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }

    /**
     * @return BelongsTo<CampaignTopic, $this>
     */
    public function topic(): BelongsTo
    {
        return $this->belongsTo(CampaignTopic::class, 'campaign_topic_id');
    }

    /**
     * @return BelongsTo<LinkedInAccount, $this>
     */
    public function linkedinAccount(): BelongsTo
    {
        return $this->belongsTo(LinkedInAccount::class);
    }

    /**
     * @return BelongsTo<PostVersion, $this>
     */
    public function currentVersion(): BelongsTo
    {
        return $this->belongsTo(PostVersion::class, 'current_version_id');
    }

    /**
     * @return HasMany<PostVersion, $this>
     */
    public function versions(): HasMany
    {
        return $this->hasMany(PostVersion::class)->orderByDesc('version_number');
    }

    /**
     * @return HasMany<PostImage, $this>
     */
    public function images(): HasMany
    {
        return $this->hasMany(PostImage::class);
    }

    public function isPublished(): bool
    {
        return $this->status === PostStatus::Published || filled($this->linkedin_post_id);
    }
}
