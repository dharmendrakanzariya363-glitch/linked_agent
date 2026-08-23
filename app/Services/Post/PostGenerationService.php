<?php

namespace App\Services\Post;

use App\Data\GeneratedCopy;
use App\Enums\CampaignStatus;
use App\Enums\ImageGenerationType;
use App\Enums\PostStatus;
use App\Enums\PostVersionType;
use App\Events\PostFailed;
use App\Events\PostGenerated;
use App\Exceptions\AIGenerationException;
use App\Exceptions\DuplicatePostException;
use App\Exceptions\LinkedInException;
use App\Models\Campaign;
use App\Models\CampaignTopic;
use App\Models\Post;
use App\Models\PostVersion;
use App\Services\AI\AIService;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;
use Throwable;

class PostGenerationService
{
    public function __construct(private AIService $ai) {}

    public function generateForDate(Campaign $campaign, string $date): Post
    {
        $post = $this->claimDailyPost($campaign, $date);

        try {
            $this->fillGeneratedContent($post, PostVersionType::Initial, null);
        } catch (Throwable $e) {
            $this->markFailed($post, $e);
            throw $e;
        }

        return $post->refresh()->load(['currentVersion.image', 'topic', 'campaign']);
    }

    public function regenerateDescription(Post $post): Post
    {
        $this->assertCanMutate($post);

        return $this->runMutation($post, function (Post $post): void {
            $this->fillGeneratedContent($post, PostVersionType::Regenerated, 'Regenerate description');
        });
    }

    public function regenerateImage(Post $post): Post
    {
        $this->assertCanMutate($post);
        $this->assertHasImageCampaign($post);

        return $this->runMutation($post, function (Post $post): void {
            $current = $this->currentVersion($post);
            $copy = new GeneratedCopy($current->description, [], (string) $current->image?->prompt);
            $version = $this->createVersion($post, $copy->description, PostVersionType::Regenerated, 'Regenerate image');
            $this->ai->regenerateImage($post->campaign, $this->topic($post), $current, $post, $version);
            $this->activateVersion($post, $version);
        });
    }

    public function editWithAi(Post $post, string $instruction): Post
    {
        $this->assertCanMutate($post);

        return $this->runMutation($post, function (Post $post) use ($instruction): void {
            $current = $this->currentVersion($post);
            $copy = $this->ai->editCopy($current, $instruction);
            $version = $this->createVersion($post, $copy->body(), PostVersionType::AiEdited, $instruction);

            if ($post->campaign->requiresImage()) {
                $this->ai->generateImage($post->campaign, $this->topic($post), $copy->body(), $copy->imagePrompt, $post, $version, ImageGenerationType::Generated);
            }

            $this->activateVersion($post, $version);
        });
    }

    public function editImageWithAi(Post $post, string $instruction): Post
    {
        $this->assertCanMutate($post);
        $this->assertHasImageCampaign($post);

        return $this->runMutation($post, function (Post $post) use ($instruction): void {
            $current = $this->currentVersion($post);
            $version = $this->createVersion($post, $current->description, PostVersionType::AiEdited, $instruction);
            $this->ai->editImage($current, $instruction, $post, $version);
            $this->activateVersion($post, $version);
        });
    }

    public function saveManualEdit(Post $post, string $description): Post
    {
        $this->assertCanMutate($post);

        $description = trim($description);
        $current = $this->currentVersion($post);

        if ($current->description === $description) {
            return $post->load(['currentVersion.image', 'topic', 'campaign']);
        }

        $version = $this->createVersion($post, $description, PostVersionType::Manual, null);

        if ($current->image && $post->campaign->requiresImage()) {
            $current->image->newInstance()->forceFill([
                'user_id' => $post->user_id,
                'post_id' => $post->id,
                'post_version_id' => $version->id,
                'disk' => $current->image->disk,
                'path' => $current->image->path,
                'prompt' => $current->image->prompt,
                'generation_type' => $current->image->generation_type,
            ])->save();
        }

        $this->activateVersion($post, $version);

        return $post->refresh()->load(['currentVersion.image', 'topic', 'campaign']);
    }

    public function retryFailed(Post $post): Post
    {
        if ($post->status !== PostStatus::Failed) {
            return $post;
        }

        return $this->runMutation($post, function (Post $post): void {
            $this->fillGeneratedContent($post, PostVersionType::Regenerated, 'Retry generation');
        });
    }

    private function claimDailyPost(Campaign $campaign, string $date): Post
    {
        if ($campaign->status !== CampaignStatus::Active) {
            throw AIGenerationException::failed('generate a post for an inactive campaign');
        }

        if (! $campaign->linkedinAccount?->isConnected()) {
            throw LinkedInException::notConnected();
        }

        $topic = $this->nextTopic($campaign);

        if ($topic === null) {
            throw AIGenerationException::failed('generate a post without an active topic');
        }

        try {
            $post = DB::transaction(function () use ($campaign, $date, $topic): Post {
                $existing = Post::query()
                    ->where('campaign_id', $campaign->id)
                    ->whereDate('scheduled_for', $date)
                    ->lockForUpdate()
                    ->first();

                if ($existing !== null) {
                    if ($existing->status === PostStatus::Generating
                        && $existing->updated_at?->lt(now()->subMinutes((int) config('content.generation.stale_after_minutes', 20)))) {
                        return $existing;
                    }

                    throw DuplicatePostException::forDate($date);
                }

                return Post::query()->create([
                    'user_id' => $campaign->user_id,
                    'campaign_id' => $campaign->id,
                    'campaign_topic_id' => $topic->id,
                    'linkedin_account_id' => $campaign->linkedin_account_id,
                    'status' => PostStatus::Generating,
                    'scheduled_for' => $date,
                ]);
            });
        } catch (UniqueConstraintViolationException) {
            throw DuplicatePostException::forDate($date);
        }

        $topic->forceFill(['last_used_at' => now()])->save();

        return $post;
    }

    private function fillGeneratedContent(Post $post, PostVersionType $type, ?string $prompt): void
    {
        $post->load(['campaign.linkedinAccount', 'topic']);
        $campaign = $post->campaign;
        $topic = $this->topic($post);

        $copy = match ($type) {
            PostVersionType::AiEdited => throw new \LogicException('Use editWithAi for AI edits.'),
            default => $this->aiCopy($campaign, $topic, $post, $type),
        };

        $version = $this->createVersion($post, $copy->body(), $type, $prompt);

        if ($campaign->requiresImage()) {
            $this->ai->generateImage(
                $campaign,
                $topic,
                $copy->body(),
                $copy->imagePrompt,
                $post,
                $version,
                $type === PostVersionType::Initial ? ImageGenerationType::Generated : ImageGenerationType::Regenerated,
            );
        }

        $this->activateVersion($post, $version);
        event(new PostGenerated($post->refresh()));
    }

    private function aiCopy(Campaign $campaign, CampaignTopic $topic, Post $post, PostVersionType $type): GeneratedCopy
    {
        if ($type === PostVersionType::Regenerated && $post->currentVersion) {
            return $this->ai->regenerateCopy($campaign, $topic, $post->currentVersion);
        }

        return $this->ai->generateCopy($campaign, $topic);
    }

    private function createVersion(Post $post, string $description, PostVersionType $type, ?string $prompt): PostVersion
    {
        $next = (int) $post->versions()->max('version_number') + 1;

        return $post->versions()->create([
            'user_id' => $post->user_id,
            'version_number' => $next,
            'type' => $type,
            'prompt' => $prompt,
            'description' => $description,
        ]);
    }

    private function activateVersion(Post $post, PostVersion $version): void
    {
        $post->forceFill([
            'current_version_id' => $version->id,
            'status' => PostStatus::Ready,
            'generated_at' => now(),
            'last_error' => null,
        ])->save();
    }

    /**
     * @param  callable(Post): void  $callback
     */
    private function runMutation(Post $post, callable $callback): Post
    {
        $post->forceFill(['status' => PostStatus::Generating, 'last_error' => null])->save();

        try {
            $callback($post->refresh());
        } catch (Throwable $e) {
            $this->markFailed($post, $e);
            throw $e;
        }

        return $post->refresh()->load(['currentVersion.image', 'topic', 'campaign']);
    }

    private function markFailed(Post $post, Throwable $e): void
    {
        $post->forceFill([
            'status' => PostStatus::Failed,
            'last_error' => $e instanceof AIGenerationException || $e instanceof LinkedInException
                ? $e->getMessage()
                : 'Generation failed. Please retry.',
        ])->save();

        event(new PostFailed($post, $e));
    }

    private function nextTopic(Campaign $campaign): ?CampaignTopic
    {
        return $campaign->topics()
            ->where('is_active', true)
            ->orderByRaw('last_used_at is null desc')
            ->orderBy('last_used_at')
            ->orderBy('sort_order')
            ->first();
    }

    private function currentVersion(Post $post): PostVersion
    {
        $version = $post->currentVersion ?? $post->versions()->first();

        if ($version === null) {
            throw AIGenerationException::failed('edit a post that has no content yet');
        }

        return $version;
    }

    private function topic(Post $post): CampaignTopic
    {
        $topic = $post->topic ?? $this->nextTopic($post->campaign);

        if ($topic === null) {
            throw AIGenerationException::failed('use a campaign topic');
        }

        return $topic;
    }

    public function assertEditable(Post $post): void
    {
        $this->assertCanMutate($post);
    }

    private function assertCanMutate(Post $post): void
    {
        if ($post->status === PostStatus::Generating) {
            throw AIGenerationException::failed('start another generation while one is already running');
        }

        if (in_array($post->status, [PostStatus::Publishing, PostStatus::Published], true)) {
            throw AIGenerationException::failed('edit a post that is already publishing or published');
        }
    }

    private function assertHasImageCampaign(Post $post): void
    {
        if (! $post->campaign->requiresImage()) {
            throw AIGenerationException::failed('generate an image for a description-only campaign');
        }
    }
}
