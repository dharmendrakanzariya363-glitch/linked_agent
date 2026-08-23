<?php

namespace App\Http\Controllers;

use App\Enums\CampaignStatus;
use App\Enums\PostStatus;
use App\Http\Resources\CampaignResource;
use App\Http\Resources\LinkedInAccountResource;
use App\Http\Resources\PostResource;
use App\Models\Post;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $user = $request->user();
        $today = now()->toDateString();

        $campaigns = $user->campaigns()
            ->with('linkedinAccount')
            ->withCount('posts')
            ->latest()
            ->get();

        $todayPosts = Post::query()
            ->where('user_id', $user->id)
            ->whereDate('scheduled_for', $today)
            ->with(['campaign', 'topic', 'currentVersion.image'])
            ->latest()
            ->get();

        $recentPosts = Post::query()
            ->where('user_id', $user->id)
            ->with(['campaign', 'topic', 'currentVersion.image'])
            ->latest()
            ->limit(6)
            ->get();

        $nextCampaign = $user->campaigns()
            ->where('status', CampaignStatus::Active)
            ->orderBy('daily_post_time')
            ->first();

        return Inertia::render('dashboard', [
            'linkedinConnected' => $user->hasConnectedLinkedIn(),
            'linkedinAccount' => $user->connectedLinkedInAccount()
                ? new LinkedInAccountResource($user->connectedLinkedInAccount())
                : null,
            'campaigns' => CampaignResource::collection($campaigns),
            'todayPosts' => PostResource::collection($todayPosts),
            'recentPosts' => PostResource::collection($recentPosts),
            'stats' => [
                'active_campaigns' => $campaigns->where('status', CampaignStatus::Active)->count(),
                'ready_posts' => $todayPosts->where('status', PostStatus::Ready)->count(),
                'published_posts' => Post::query()->where('user_id', $user->id)->where('status', PostStatus::Published)->count(),
            ],
            'nextCampaign' => $nextCampaign ? new CampaignResource($nextCampaign) : null,
        ]);
    }
}
