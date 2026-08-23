<?php

namespace App\Services\Topic;

use App\Models\Campaign;
use App\Models\CampaignTopic;

class TopicService
{
    public function store(Campaign $campaign, string $title): CampaignTopic
    {
        $max = (int) $campaign->topics()->max('sort_order');

        return $campaign->topics()->create([
            'title' => trim($title),
            'is_active' => true,
            'sort_order' => $max + 1,
        ]);
    }

    public function update(CampaignTopic $topic, string $title, bool $isActive): CampaignTopic
    {
        $topic->forceFill([
            'title' => trim($title),
            'is_active' => $isActive,
        ])->save();

        return $topic;
    }

    /**
     * @param  list<int>  $orderedIds
     */
    public function reorder(Campaign $campaign, array $orderedIds): void
    {
        foreach ($orderedIds as $index => $id) {
            $campaign->topics()->whereKey($id)->update(['sort_order' => $index]);
        }
    }

    public function delete(CampaignTopic $topic): void
    {
        $topic->delete();
    }
}
