<?php

namespace App\Services\Post;

use App\Enums\PostStatus;
use App\Events\PostPublished;
use App\Exceptions\DuplicatePublishException;
use App\Exceptions\LinkedInException;
use App\Jobs\PublishLinkedInPostJob;
use App\Models\Post;
use App\Services\LinkedIn\LinkedInService;
use Illuminate\Support\Facades\DB;
use Throwable;

class PostPublishingService
{
    public function __construct(private LinkedInService $linkedin) {}

    public function approve(Post $post): Post
    {
        $post->load(['currentVersion.image', 'campaign', 'linkedinAccount']);

        if ($post->status === PostStatus::Published || filled($post->linkedin_post_id)) {
            throw DuplicatePublishException::alreadyPublished();
        }

        if (! $post->status->canApprove()) {
            throw LinkedInException::publishFailed('This post is not ready to approve.');
        }

        $this->assertPublishable($post);

        $post->forceFill([
            'status' => PostStatus::Approved,
            'approved_at' => now(),
        ])->save();

        PublishLinkedInPostJob::dispatch($post->id);

        return $post->refresh();
    }

    public function publish(Post $post): Post
    {
        $claimed = DB::transaction(function () use ($post): ?Post {
            $locked = Post::query()->whereKey($post->id)->lockForUpdate()->first();

            if ($locked === null) {
                return null;
            }

            if ($locked->status === PostStatus::Published || filled($locked->linkedin_post_id)) {
                return $locked;
            }

            if ($locked->status === PostStatus::Publishing) {
                throw DuplicatePublishException::alreadyPublishing();
            }

            if ($locked->status !== PostStatus::Approved) {
                throw LinkedInException::publishFailed('This post must be approved before publishing.');
            }

            $locked->forceFill(['status' => PostStatus::Publishing])->save();

            return $locked;
        });

        if ($claimed === null) {
            return $post;
        }

        if ($claimed->status === PostStatus::Published) {
            return $claimed;
        }

        try {
            $claimed->load(['currentVersion.image', 'campaign', 'linkedinAccount']);
            $this->assertPublishable($claimed);

            $result = $this->linkedin->publish($claimed);

            $claimed->forceFill([
                'status' => PostStatus::Published,
                'published_at' => now(),
                'linkedin_post_id' => $result['id'],
                'published_url' => $result['url'],
                'last_error' => null,
            ])->save();

            event(new PostPublished($claimed));

            return $claimed->refresh();
        } catch (Throwable $e) {
            $claimed->forceFill([
                'status' => PostStatus::Failed,
                'last_error' => $e instanceof LinkedInException
                    ? $e->getMessage()
                    : 'Publishing failed. You can retry from the post page.',
            ])->save();

            throw $e;
        }
    }

    public function retry(Post $post): Post
    {
        if ($post->status === PostStatus::Published || filled($post->linkedin_post_id)) {
            throw DuplicatePublishException::alreadyPublished();
        }

        $post->load(['currentVersion.image', 'campaign', 'linkedinAccount']);
        $this->assertPublishable($post);

        $post->forceFill([
            'status' => PostStatus::Approved,
            'last_error' => null,
        ])->save();

        PublishLinkedInPostJob::dispatch($post->id);

        return $post->refresh();
    }

    private function assertPublishable(Post $post): void
    {
        if (! $post->linkedinAccount?->isConnected()) {
            throw LinkedInException::notConnected();
        }

        $version = $post->currentVersion;

        if ($version === null || blank($version->description)) {
            throw LinkedInException::publishFailed('This post does not have any content to publish.');
        }

        if ($post->campaign->requiresImage() && $version->image === null) {
            throw LinkedInException::publishFailed('This post needs an image before it can be published.');
        }
    }
}
