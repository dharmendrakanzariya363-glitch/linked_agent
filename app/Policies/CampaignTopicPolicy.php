<?php

namespace App\Policies;

use App\Models\Campaign;
use App\Models\CampaignTopic;
use App\Models\User;

class CampaignTopicPolicy
{
    public function create(User $user, Campaign $campaign): bool
    {
        return $user->id === $campaign->user_id;
    }

    public function update(User $user, CampaignTopic $topic): bool
    {
        return $user->id === $topic->campaign->user_id;
    }

    public function delete(User $user, CampaignTopic $topic): bool
    {
        return $user->id === $topic->campaign->user_id;
    }
}
