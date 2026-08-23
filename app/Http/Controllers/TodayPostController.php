<?php

namespace App\Http\Controllers;

use App\Http\Resources\PostResource;
use App\Models\Post;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TodayPostController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $post = Post::query()
            ->where('user_id', $request->user()->id)
            ->whereDate('scheduled_for', now()->toDateString())
            ->with(['campaign', 'topic', 'currentVersion.image', 'versions.image'])
            ->latest()
            ->first();

        return Inertia::render('posts/today', [
            'post' => $post ? new PostResource($post) : null,
        ]);
    }
}
