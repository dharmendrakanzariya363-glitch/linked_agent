<?php

namespace App\Jobs;

use App\Models\Post;
use App\Services\Post\PostGenerationService;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class EditPostImageWithAiJob implements ShouldBeUnique, ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $timeout = 180;

    public function __construct(public int $postId, public string $instruction) {}

    public function uniqueId(): string
    {
        return 'ai-edit-image:'.$this->postId;
    }

    public function handle(PostGenerationService $generator): void
    {
        $post = Post::query()->with(['campaign', 'topic', 'currentVersion.image'])->find($this->postId);

        if ($post === null) {
            return;
        }

        $generator->editImageWithAi($post, $this->instruction);
    }
}
