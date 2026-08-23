<?php

namespace Tests\Feature;

use App\Enums\PostStatus;
use App\Jobs\PublishLinkedInPostJob;
use App\Models\Campaign;
use App\Models\LinkedInAccount;
use App\Models\Post;
use App\Models\PostVersion;
use App\Models\User;
use App\Services\Post\PostPublishingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class PostPublishingTest extends TestCase
{
    use RefreshDatabase;

    public function test_approve_dispatches_a_single_publish_job(): void
    {
        Queue::fake();
        $post = $this->readyPost();

        $this->actingAs($post->user)
            ->post(route('posts.approve', $post))
            ->assertRedirect();

        $this->actingAs($post->user)
            ->post(route('posts.approve', $post));

        Queue::assertPushed(PublishLinkedInPostJob::class, 1);
        $this->assertSame(PostStatus::Approved, $post->refresh()->status);
    }

    public function test_publish_is_idempotent_once_linkedin_returns_an_id(): void
    {
        Http::fake([
            'https://api.linkedin.com/rest/posts' => Http::response(['id' => 'urn:li:share:99'], 201, ['x-restli-id' => 'urn:li:share:99']),
            '*' => Http::response([], 200),
        ]);

        $post = $this->readyPost();
        $post->forceFill(['status' => PostStatus::Approved])->save();

        $service = app(PostPublishingService::class);
        $first = $service->publish($post);
        $second = $service->publish($post->refresh());

        $this->assertSame(PostStatus::Published, $first->status);
        $this->assertSame($first->linkedin_post_id, $second->linkedin_post_id);
        Http::assertSentCount(1);
    }

    public function test_another_user_cannot_approve_a_post(): void
    {
        $post = $this->readyPost();
        $other = User::factory()->create();

        $this->actingAs($other)
            ->post(route('posts.approve', $post))
            ->assertForbidden();
    }

    private function readyPost(): Post
    {
        $user = User::factory()->create();
        $account = LinkedInAccount::factory()->create(['user_id' => $user->id]);
        $campaign = Campaign::factory()->create([
            'user_id' => $user->id,
            'linkedin_account_id' => $account->id,
        ]);
        $post = Post::factory()->create([
            'user_id' => $user->id,
            'campaign_id' => $campaign->id,
            'linkedin_account_id' => $account->id,
            'status' => PostStatus::Ready,
        ]);
        $version = PostVersion::factory()->create([
            'post_id' => $post->id,
            'user_id' => $user->id,
            'description' => 'A ready LinkedIn post.',
        ]);
        $post->forceFill(['current_version_id' => $version->id])->save();

        return $post->refresh();
    }
}
