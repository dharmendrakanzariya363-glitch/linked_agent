<?php

namespace Database\Factories;

use App\Models\LinkedInAccount;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LinkedInAccount>
 */
class LinkedInAccountFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $id = fake()->unique()->numerify('########');

        return [
            'user_id' => User::factory(),
            'linkedin_id' => $id,
            'name' => fake()->name(),
            'email' => fake()->safeEmail(),
            'headline' => fake()->jobTitle(),
            'profile_url' => 'https://www.linkedin.com/in/'.fake()->userName(),
            'avatar_url' => null,
            'access_token' => 'test-access-token',
            'refresh_token' => 'test-refresh-token',
            'token_expires_at' => now()->addDays(30),
            'scopes' => ['openid', 'profile', 'email', 'w_member_social'],
            'disconnected_at' => null,
        ];
    }

    public function disconnected(): static
    {
        return $this->state(fn (array $attributes): array => [
            'access_token' => null,
            'refresh_token' => null,
            'disconnected_at' => now(),
        ]);
    }

    public function expired(): static
    {
        return $this->state(fn (array $attributes): array => [
            'token_expires_at' => now()->subHour(),
        ]);
    }
}
