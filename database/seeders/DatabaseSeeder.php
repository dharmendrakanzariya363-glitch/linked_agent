<?php

namespace Database\Seeders;

use App\Enums\CampaignStatus;
use App\Enums\ContentType;
use App\Models\Campaign;
use App\Models\CampaignTopic;
use App\Models\LinkedInAccount;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::factory()->create([
            'name' => 'Demo User',
            'email' => 'demo@linkd.test',
            'password' => 'password',
        ]);

        $account = LinkedInAccount::factory()->create([
            'user_id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
        ]);

        $campaign = Campaign::factory()->create([
            'user_id' => $user->id,
            'linkedin_account_id' => $account->id,
            'name' => 'Laravel Daily',
            'timezone' => 'UTC',
            'daily_post_time' => '10:00:00',
            'content_type' => ContentType::DescriptionImage,
            'status' => CampaignStatus::Draft,
        ]);

        foreach (['Laravel tips', 'Eloquent performance', 'Queue jobs', 'Testing'] as $index => $title) {
            CampaignTopic::factory()->create([
                'campaign_id' => $campaign->id,
                'title' => $title,
                'sort_order' => $index,
            ]);
        }
    }
}
