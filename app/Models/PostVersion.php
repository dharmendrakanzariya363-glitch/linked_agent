<?php

namespace App\Models;

use App\Enums\PostVersionType;
use Database\Factories\PostVersionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $post_id
 * @property int $user_id
 * @property int $version_number
 * @property PostVersionType $type
 * @property string|null $prompt
 * @property string $description
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['post_id', 'user_id', 'version_number', 'type', 'prompt', 'description'])]
class PostVersion extends Model
{
    /** @use HasFactory<PostVersionFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => PostVersionType::class,
            'version_number' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<Post, $this>
     */
    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return HasOne<PostImage, $this>
     */
    public function image(): HasOne
    {
        return $this->hasOne(PostImage::class);
    }
}
