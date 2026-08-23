<?php

namespace Database\Factories;

use App\Models\Campaign;
use App\Models\CampaignTopic;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CampaignTopic>
 */
class CampaignTopicFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'campaign_id' => Campaign::factory(),
            'title' => fake()->unique()->words(2, true),
            'is_active' => true,
            'sort_order' => 0,
            'last_used_at' => null,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes): array => [
            'is_active' => false,
        ]);
    }
}
