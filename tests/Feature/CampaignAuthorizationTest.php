<?php

namespace Tests\Feature;

use App\Models\Campaign;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CampaignAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_users_cannot_view_another_users_campaign(): void
    {
        $owner = User::factory()->create();
        $intruder = User::factory()->create();
        $campaign = Campaign::factory()->create(['user_id' => $owner->id]);

        $this->actingAs($intruder)
            ->get(route('campaigns.show', $campaign))
            ->assertForbidden();
    }

    public function test_users_can_view_their_own_campaign(): void
    {
        $user = User::factory()->create();
        $campaign = Campaign::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user)
            ->get(route('campaigns.show', $campaign))
            ->assertOk();
    }
}
