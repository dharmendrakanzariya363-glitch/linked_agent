<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCampaignRequest;
use App\Http\Requests\UpdateCampaignRequest;
use App\Http\Resources\CampaignResource;
use App\Http\Resources\LinkedInAccountResource;
use App\Http\Resources\PostResource;
use App\Models\Campaign;
use App\Services\Campaign\CampaignService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CampaignController extends Controller
{
    public function __construct(private CampaignService $campaigns) {}

    public function index(Request $request): Response
    {
        $campaigns = $request->user()
            ->campaigns()
            ->with(['linkedinAccount', 'topics'])
            ->withCount('posts')
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return Inertia::render('campaigns/index', [
            'campaigns' => CampaignResource::collection($campaigns),
        ]);
    }

    public function create(Request $request): Response
    {
        return Inertia::render('campaigns/create', [
            'accounts' => LinkedInAccountResource::collection(
                $request->user()->linkedinAccounts()->connected()->get()
            ),
            'timezones' => timezone_identifiers_list(),
        ]);
    }

    public function store(StoreCampaignRequest $request): RedirectResponse
    {
        $campaign = $this->campaigns->create(
            $request->user(),
            $request->safe()->except('topics'),
            $request->validated('topics'),
        );

        return redirect()->route('campaigns.show', $campaign)
            ->with('success', 'Campaign created.');
    }

    public function show(Request $request, Campaign $campaign): Response
    {
        $this->authorize('view', $campaign);

        $campaign->load(['linkedinAccount', 'topics']);

        $posts = $campaign->posts()
            ->with(['topic', 'currentVersion.image'])
            ->latest('scheduled_for')
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('campaigns/show', [
            'campaign' => new CampaignResource($campaign),
            'posts' => PostResource::collection($posts),
        ]);
    }

    public function edit(Campaign $campaign): Response
    {
        $this->authorize('update', $campaign);

        $campaign->load(['topics', 'linkedinAccount']);

        return Inertia::render('campaigns/edit', [
            'campaign' => new CampaignResource($campaign),
            'accounts' => LinkedInAccountResource::collection(
                $campaign->user->linkedinAccounts()->connected()->get()
            ),
            'timezones' => timezone_identifiers_list(),
        ]);
    }

    public function update(UpdateCampaignRequest $request, Campaign $campaign): RedirectResponse
    {
        $this->campaigns->update(
            $campaign,
            $request->safe()->except('topics'),
            $request->validated('topics'),
        );

        return redirect()->route('campaigns.show', $campaign)
            ->with('success', 'Campaign updated.');
    }

    public function destroy(Campaign $campaign): RedirectResponse
    {
        $this->authorize('delete', $campaign);

        $campaign->delete();

        return redirect()->route('campaigns.index')
            ->with('success', 'Campaign deleted.');
    }

    public function activate(Campaign $campaign): RedirectResponse
    {
        $this->authorize('update', $campaign);
        $this->campaigns->activate($campaign);

        return back()->with('success', 'Campaign is now active.');
    }

    public function pause(Campaign $campaign): RedirectResponse
    {
        $this->authorize('update', $campaign);
        $this->campaigns->pause($campaign);

        return back()->with('success', 'Campaign paused.');
    }
}
