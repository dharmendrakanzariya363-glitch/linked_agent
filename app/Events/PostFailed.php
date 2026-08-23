<?php

namespace App\Events;

use App\Models\Post;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Throwable;

class PostFailed
{
    use Dispatchable, SerializesModels;

    public function __construct(public Post $post, public Throwable $exception) {}
}
