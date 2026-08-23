<?php

namespace App\Models;

use App\Enums\ImageGenerationType;
use Database\Factories\PostImageFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

/**
 * @property int $id
 * @property int $user_id
 * @property int $post_id
 * @property int $post_version_id
 * @property string $disk
 * @property string $path
 * @property string|null $prompt
 * @property ImageGenerationType $generation_type
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read string $url
 */
#[Fillable(['user_id', 'post_id', 'post_version_id', 'disk', 'path', 'prompt', 'generation_type'])]
class PostImage extends Model
{
    /** @use HasFactory<PostImageFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'generation_type' => ImageGenerationType::class,
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
     * @return BelongsTo<Post, $this>
     */
    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }

    /**
     * @return BelongsTo<PostVersion, $this>
     */
    public function version(): BelongsTo
    {
        return $this->belongsTo(PostVersion::class, 'post_version_id');
    }

    /**
     * @return Attribute<string, never>
     */
    protected function url(): Attribute
    {
        return Attribute::get(fn (): string => Storage::disk($this->disk)->url($this->path));
    }
}
