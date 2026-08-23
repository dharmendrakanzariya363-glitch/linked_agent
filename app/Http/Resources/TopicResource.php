<?php

namespace App\Http\Resources;

use App\Models\CampaignTopic;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin CampaignTopic
 */
class TopicResource extends JsonResource
{
    public static $wrap = null;

    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'is_active' => $this->is_active,
            'sort_order' => $this->sort_order,
            'last_used_at' => $this->last_used_at?->toIso8601String(),
        ];
    }
}
