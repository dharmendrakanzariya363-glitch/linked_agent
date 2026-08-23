<?php

namespace App\Http\Resources;

use App\Models\Campaign;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Campaign
 */
class CampaignResource extends JsonResource
{
    public static $wrap = null;

    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'timezone' => $this->timezone,
            'daily_post_time' => substr((string) $this->daily_post_time, 0, 5),
            'start_date' => $this->start_date->toDateString(),
            'end_date' => $this->end_date?->toDateString(),
            'content_type' => $this->content_type->value,
            'content_type_label' => $this->content_type->label(),
            'status' => $this->status->value,
            'status_label' => $this->status->label(),
            'linkedin_account_id' => $this->linkedin_account_id,
            'linkedin_account' => $this->whenLoaded('linkedinAccount', fn () => new LinkedInAccountResource($this->linkedinAccount)),
            'topics' => $this->whenLoaded('topics', fn () => TopicResource::collection($this->topics)),
            'posts_count' => $this->whenCounted('posts'),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
