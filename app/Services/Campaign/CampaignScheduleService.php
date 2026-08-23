<?php

namespace App\Services\Campaign;

use App\Enums\CampaignStatus;
use App\Jobs\GenerateDailyPostJob;
use App\Models\Campaign;
use App\Models\Post;
use Illuminate\Support\Carbon;

class CampaignScheduleService
{
    public function dispatchDueCampaigns(): int
    {
        $dispatched = 0;

        Campaign::query()
            ->with(['linkedinAccount', 'topics'])
            ->where('status', CampaignStatus::Active)
            ->orderBy('id')
            ->chunkById(50, function ($campaigns) use (&$dispatched): void {
                foreach ($campaigns as $campaign) {
                    if ($this->dispatchIfDue($campaign)) {
                        $dispatched++;
                    }
                }
            });

        return $dispatched;
    }

    public function dispatchIfDue(Campaign $campaign): bool
    {
        if ($campaign->status !== CampaignStatus::Active) {
            return false;
        }

        if (! $campaign->linkedinAccount?->isConnected()) {
            return false;
        }

        if ($campaign->topics->where('is_active', true)->isEmpty()) {
            return false;
        }

        $localNow = Carbon::now($campaign->timezone);
        $today = $localNow->toDateString();

        if ($campaign->start_date->toDateString() > $today) {
            return false;
        }

        if ($campaign->end_date !== null && $campaign->end_date->toDateString() < $today) {
            return false;
        }

        if ($localNow->format('H:i:s') < $this->normalizedTime($campaign->daily_post_time)) {
            return false;
        }

        $exists = Post::query()
            ->where('campaign_id', $campaign->id)
            ->whereDate('scheduled_for', $today)
            ->exists();

        if ($exists) {
            return false;
        }

        GenerateDailyPostJob::dispatch($campaign->id, $today);

        return true;
    }

    private function normalizedTime(string $time): string
    {
        return strlen($time) === 5 ? $time.':00' : $time;
    }
}
