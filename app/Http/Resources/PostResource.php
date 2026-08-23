<?php

namespace App\Http\Resources;

use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Post
 */
class PostResource extends JsonResource
{
    public static $wrap = null;

    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'status' => $this->status->value,
            'status_label' => $this->status->label(),
            'scheduled_for' => $this->scheduled_for->toDateString(),
            'generated_at' => $this->generated_at?->toIso8601String(),
            'approved_at' => $this->approved_at?->toIso8601String(),
            'published_at' => $this->published_at?->toIso8601String(),
            'published_url' => $this->published_url,
            'last_error' => $this->last_error,
            'current_version_id' => $this->current_version_id,
            'campaign' => $this->whenLoaded('campaign', fn () => [
                'id' => $this->campaign->id,
                'name' => $this->campaign->name,
                'content_type' => $this->campaign->content_type->value,
                'requires_image' => $this->campaign->requiresImage(),
            ]),
            'topic' => $this->whenLoaded('topic', fn () => $this->topic ? [
                'id' => $this->topic->id,
                'title' => $this->topic->title,
            ] : null),
            'current_version' => $this->whenLoaded('currentVersion', fn () => $this->currentVersion
                ? new PostVersionResource($this->currentVersion)
                : null),
            'versions' => $this->whenLoaded('versions', fn () => PostVersionResource::collection($this->versions)),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
