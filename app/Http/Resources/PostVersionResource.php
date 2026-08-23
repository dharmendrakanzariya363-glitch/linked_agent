<?php

namespace App\Http\Resources;

use App\Models\PostVersion;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin PostVersion
 */
class PostVersionResource extends JsonResource
{
    public static $wrap = null;

    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'version_number' => $this->version_number,
            'type' => $this->type->value,
            'type_label' => $this->type->label(),
            'prompt' => $this->prompt,
            'description' => $this->description,
            'image' => $this->whenLoaded('image', fn () => $this->image ? new PostImageResource($this->image) : null),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
