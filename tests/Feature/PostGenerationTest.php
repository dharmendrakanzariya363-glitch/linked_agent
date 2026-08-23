<?php

namespace Tests\Feature;

use App\Agents\ContentAgent;
use App\Enums\CampaignStatus;
use App\Enums\ContentType;
use App\Enums\PostStatus;
use App\Exceptions\DuplicatePostException;
use App\Jobs\GenerateDailyPostJob;
use App\Models\Campaign;
use App\Models\CampaignTopic;
use App\Models\LinkedInAccount;
use App\Models\Post;
use App\Models\User;
use App\Services\Campaign\CampaignScheduleService;
use App\Services\Post\PostGenerationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Laravel\Ai\Image;
use Tests\TestCase;

class PostGenerationTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_does_not_create_duplicate_posts_for_the_same_campaign_and_date(): void
    {
        $campaign = $this->activeCampaign();
        $this->fakeAi();

        $first = app(PostGenerationService::class)->generateForDate($campaign, '2026-08-23');

        $this->expectException(DuplicatePostException::class);
        app(PostGenerationService::class)->generateForDate($campaign, '2026-08-23');

        $this->assertSame(1, Post::query()->where('campaign_id', $campaign->id)->count());
        $this->assertSame($first->id, Post::query()->first()->id);
    }

    public function test_scheduler_dispatches_a_job_once_per_due_campaign(): void
    {
        $campaign = $this->activeCampaign();
        $campaign->forceFill([
            'timezone' => 'UTC',
            'daily_post_time' => now('UTC')->subMinute()->format('H:i:s'),
            'start_date' => now('UTC')->toDateString(),
        ])->save();

        Queue::fake();

        app(CampaignScheduleService::class)->dispatchDueCampaigns();
        app(CampaignScheduleService::class)->dispatchDueCampaigns();

        Queue::assertPushed(GenerateDailyPostJob::class, 1);
    }

    public function test_generation_creates_a_ready_post_and_version(): void
    {
        $campaign = $this->activeCampaign();
        $this->fakeAi();

        $post = app(PostGenerationService::class)->generateForDate($campaign, now()->toDateString());

        $this->assertSame(PostStatus::Ready, $post->status);
        $this->assertNotNull($post->current_version_id);
        $this->assertDatabaseCount('post_versions', 1);
        $this->assertDatabaseHas('notifications', ['notifiable_id' => $campaign->user_id]);
    }

    private function activeCampaign(): Campaign
    {
        $user = User::factory()->create();
        $account = LinkedInAccount::factory()->create(['user_id' => $user->id]);
        $campaign = Campaign::factory()->create([
            'user_id' => $user->id,
            'linkedin_account_id' => $account->id,
            'status' => CampaignStatus::Active,
            'content_type' => ContentType::Description,
            'timezone' => 'UTC',
            'daily_post_time' => '00:00:00',
        ]);
        CampaignTopic::factory()->create(['campaign_id' => $campaign->id]);

        return $campaign->refresh();
    }

    private function fakeAi(): void
    {
        ContentAgent::fake([
            [
                'description' => 'A practical LinkedIn post about Laravel queues.',
                'hashtags' => ['#Laravel', '#PHP'],
                'image_prompt' => 'A developer at a quiet desk',
            ],
        ]);
        Image::fake();
        Http::fake();
    }
}
