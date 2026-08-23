<?php

namespace App\Services\Campaign;

use App\Enums\CampaignStatus;
use App\Exceptions\LinkedInException;
use App\Models\Campaign;
use App\Models\LinkedInAccount;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CampaignService
{
    /**
     * @param  array<string, mixed>  $data
     * @param  list<string>  $topics
     */
    public function create(User $user, array $data, array $topics): Campaign
    {
        $account = $this->ownedAccount($user, (int) $data['linkedin_account_id']);

        return DB::transaction(function () use ($user, $data, $topics, $account): Campaign {
            $campaign = Campaign::query()->create([
                'user_id' => $user->id,
                'linkedin_account_id' => $account->id,
                'name' => $data['name'],
                'timezone' => $data['timezone'],
                'daily_post_time' => $data['daily_post_time'],
                'start_date' => $data['start_date'],
                'end_date' => $data['end_date'] ?? null,
                'content_type' => $data['content_type'],
                'status' => CampaignStatus::Draft,
            ]);

            $this->syncTopics($campaign, $topics);

            return $campaign->load(['topics', 'linkedinAccount']);
        });
    }

    /**
     * @param  array<string, mixed>  $data
     * @param  list<string>|null  $topics
     */
    public function update(Campaign $campaign, array $data, ?array $topics = null): Campaign
    {
        $account = $this->ownedAccount($campaign->user, (int) $data['linkedin_account_id']);

        $campaign->fill([
            'linkedin_account_id' => $account->id,
            'name' => $data['name'],
            'timezone' => $data['timezone'],
            'daily_post_time' => $data['daily_post_time'],
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'] ?? null,
            'content_type' => $data['content_type'],
        ])->save();

        if ($topics !== null) {
            $this->syncTopics($campaign, $topics);
        }

        return $campaign->refresh()->load(['topics', 'linkedinAccount']);
    }

    public function activate(Campaign $campaign): Campaign
    {
        $campaign->load(['linkedinAccount', 'topics']);

        if (! $campaign->linkedinAccount?->isConnected()) {
            throw LinkedInException::notConnected();
        }

        if ($campaign->topics->where('is_active', true)->isEmpty()) {
            throw ValidationException::withMessages([
                'topics' => 'Add at least one active topic before activating this campaign.',
            ]);
        }

        $campaign->forceFill(['status' => CampaignStatus::Active])->save();

        return $campaign;
    }

    public function pause(Campaign $campaign): Campaign
    {
        if ($campaign->status === CampaignStatus::Active) {
            $campaign->forceFill(['status' => CampaignStatus::Paused])->save();
        }

        return $campaign;
    }

    public function completeExpired(): int
    {
        $count = 0;

        Campaign::query()
            ->where('status', CampaignStatus::Active)
            ->whereNotNull('end_date')
            ->get()
            ->each(function (Campaign $campaign) use (&$count): void {
                if ($campaign->localToday()->toDateString() > $campaign->end_date?->toDateString()) {
                    $campaign->forceFill(['status' => CampaignStatus::Completed])->save();
                    $count++;
                }
            });

        return $count;
    }

    /**
     * @param  list<string>  $topics
     */
    public function syncTopics(Campaign $campaign, array $topics): void
    {
        $titles = array_values(array_filter(array_map(fn (string $title): string => trim($title), $topics)));

        $keep = [];

        foreach ($titles as $index => $title) {
            $topic = $campaign->topics()->where('title', $title)->first();

            if ($topic === null) {
                $topic = $campaign->topics()->create([
                    'title' => $title,
                    'is_active' => true,
                    'sort_order' => $index,
                ]);
            } else {
                $topic->forceFill(['sort_order' => $index, 'is_active' => true])->save();
            }

            $keep[] = $topic->id;
        }

        $campaign->topics()->whereNotIn('id', $keep)->delete();
    }

    private function ownedAccount(User $user, int $accountId): LinkedInAccount
    {
        $account = LinkedInAccount::query()
            ->where('user_id', $user->id)
            ->whereKey($accountId)
            ->first();

        if ($account === null || ! $account->isConnected()) {
            throw LinkedInException::notConnected();
        }

        return $account;
    }
}
