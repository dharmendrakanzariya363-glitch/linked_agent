<?php

namespace App\Notifications;

use App\Models\Post;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class PostReadyNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Post $post) {}

    /**
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $this->post->loadMissing('campaign');

        return [
            'title' => "Today's post is ready",
            'message' => "Your {$this->post->campaign->name} post is ready to review.",
            'post_id' => $this->post->id,
            'campaign_id' => $this->post->campaign_id,
            'url' => route('posts.show', $this->post),
        ];
    }
}
