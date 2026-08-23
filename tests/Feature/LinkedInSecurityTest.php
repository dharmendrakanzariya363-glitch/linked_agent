<?php

namespace Tests\Feature;

use App\Models\LinkedInAccount;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class LinkedInSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_linkedin_tokens_are_never_sent_to_inertia(): void
    {
        $user = User::factory()->create();

        LinkedInAccount::factory()->create([
            'user_id' => $user->id,
            'access_token' => 'secret-access-token',
            'refresh_token' => 'secret-refresh-token',
        ]);

        $this->actingAs($user)
            ->get(route('linkedin.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('linkedin/index')
                ->has('accounts')
                ->missing('accounts.data.0.access_token')
                ->missing('accounts.data.0.refresh_token')
                ->etc());
    }
}
