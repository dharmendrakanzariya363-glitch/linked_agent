<?php

namespace Database\Factories;

use App\Enums\ImageGenerationType;
use App\Models\Post;
use App\Models\PostImage;
use App\Models\PostVersion;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PostImage>
 */
class PostImageFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'post_id' => Post::factory(),
            'post_version_id' => PostVersion::factory(),
            'disk' => 'public',
            'path' => 'posts/1/v1.png',
            'prompt' => 'A professional illustration about Laravel.',
            'generation_type' => ImageGenerationType::Generated,
        ];
    }
}
