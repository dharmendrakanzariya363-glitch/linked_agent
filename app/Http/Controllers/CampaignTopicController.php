<?php

namespace App\Http\Controllers;

use App\Http\Requests\ReorderTopicsRequest;
use App\Http\Requests\StoreTopicRequest;
use App\Http\Requests\UpdateTopicRequest;
use App\Models\Campaign;
use App\Models\CampaignTopic;
use App\Services\Topic\TopicService;
use Illuminate\Http\RedirectResponse;

class CampaignTopicController extends Controller
{
    public function __construct(private TopicService $topics) {}

    public function store(StoreTopicRequest $request, Campaign $campaign): RedirectResponse
    {
        $this->topics->create($campaign, $request->validated('title'));

        return back()->with('success', 'Topic added.');
    }

    public function update(UpdateTopicRequest $request, CampaignTopic $topic): RedirectResponse
    {
        $this->topics->update(
            $topic,
            $request->validated('title'),
            $request->boolean('is_active'),
        );

        return back()->with('success', 'Topic updated.');
    }

    public function destroy(CampaignTopic $topic): RedirectResponse
    {
        $this->authorize('delete', $topic);
        $this->topics->delete($topic);

        return back()->with('success', 'Topic removed.');
    }

    public function reorder(ReorderTopicsRequest $request, Campaign $campaign): RedirectResponse
    {
        $this->topics->reorder($campaign, $request->validated('order'));

        return back();
    }
}
