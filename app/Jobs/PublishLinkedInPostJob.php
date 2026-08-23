<?php

namespace App\Jobs;

use App\Models\Post;
use App\Services\Post\PostPublishingService;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class PublishLinkedInPostJob implements ShouldBeUnique, ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $timeout = 90;

    /** @var list<int> */
    public array $backoff = [15, 45];

    public function __construct(public int $postId) {}

    public function uniqueId(): string
    {
        return 'publish:'.$this->postId;
    }

    public function handle(PostPublishingService $publisher): void
    {
        $post = Post::query()->find($this->postId);

        if ($post === null) {
            return;
        }

        $publisher->publish($post);
    }

    public function failed(?\Throwable $exception): void
    {
        Log::error('LinkedIn publish job failed.', [
            'post_id' => $this->postId,
            'exception' => $exception instanceof \Throwable ? $exception::class : null,
            'message' => $exception?->getMessage(),
        ]);
    }
}
