<?php

namespace Database\Factories;

use App\Enums\PostStatus;
use App\Models\Campaign;
use App\Models\CampaignTopic;
use App\Models\LinkedInAccount;
use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Post>
 */
class PostFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'campaign_id' => Campaign::factory(),
            'campaign_topic_id' => null,
            'linkedin_account_id' => LinkedInAccount::factory(),
            'current_version_id' => null,
            'status' => PostStatus::Ready,
            'scheduled_for' => now()->toDateString(),
            'generated_at' => now(),
            'approved_at' => null,
            'published_at' => null,
            'linkedin_post_id' => null,
            'published_url' => null,
            'last_error' => null,
        ];
    }

    public function generating(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => PostStatus::Generating,
            'generated_at' => null,
        ]);
    }

    public function approved(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => PostStatus::Approved,
            'approved_at' => now(),
        ]);
    }

    public function published(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => PostStatus::Published,
            'approved_at' => now()->subMinute(),
            'published_at' => now(),
            'linkedin_post_id' => 'urn:li:share:'.fake()->numerify('########'),
            'published_url' => 'https://www.linkedin.com/feed/update/urn:li:share:123',
        ]);
    }

    public function configure(): static
    {
        return $this->afterCreating(function (Post $post): void {
            if ($post->campaign && $post->campaign->user_id !== $post->user_id) {
                $post->forceFill(['user_id' => $post->campaign->user_id])->save();
            }

            if ($post->campaign && ! $post->campaign_topic_id) {
                $topic = CampaignTopic::factory()->create(['campaign_id' => $post->campaign_id]);
                $post->forceFill(['campaign_topic_id' => $topic->id])->save();
            }
        });
    }
}
