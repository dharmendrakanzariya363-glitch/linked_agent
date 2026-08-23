<?php

namespace Database\Factories;

use App\Enums\PostVersionType;
use App\Models\Post;
use App\Models\PostVersion;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PostVersion>
 */
class PostVersionFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'post_id' => Post::factory(),
            'user_id' => User::factory(),
            'version_number' => 1,
            'type' => PostVersionType::Initial,
            'prompt' => null,
            'description' => fake()->paragraphs(2, true),
        ];
    }
}
