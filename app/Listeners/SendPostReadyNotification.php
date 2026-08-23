<?php

namespace App\Listeners;

use App\Events\PostGenerated;
use App\Notifications\PostReadyNotification;

class SendPostReadyNotification
{
    public function handle(PostGenerated $event): void
    {
        $event->post->user->notify(new PostReadyNotification($event->post));
    }
}
