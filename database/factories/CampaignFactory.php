<?php

namespace Database\Factories;

use App\Enums\CampaignStatus;
use App\Enums\ContentType;
use App\Models\Campaign;
use App\Models\LinkedInAccount;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Campaign>
 */
class CampaignFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'linkedin_account_id' => LinkedInAccount::factory(),
            'name' => fake()->sentence(3),
            'timezone' => 'UTC',
            'daily_post_time' => '10:00:00',
            'start_date' => now()->toDateString(),
            'end_date' => now()->addMonth()->toDateString(),
            'content_type' => ContentType::Description,
            'status' => CampaignStatus::Draft,
        ];
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => CampaignStatus::Active,
        ]);
    }

    public function withImage(): static
    {
        return $this->state(fn (array $attributes): array => [
            'content_type' => ContentType::DescriptionImage,
        ]);
    }

    public function configure(): static
    {
        return $this->afterMaking(function (Campaign $campaign): void {
            if ($campaign->linkedin_account_id) {
                return;
            }
        })->afterCreating(function (Campaign $campaign): void {
            if ($campaign->linkedinAccount?->user_id !== $campaign->user_id) {
                $campaign->linkedinAccount()->associate(
                    LinkedInAccount::factory()->create(['user_id' => $campaign->user_id])
                );
                $campaign->save();
            }
        });
    }
}
