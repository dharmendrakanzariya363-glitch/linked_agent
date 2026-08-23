<?php

namespace App\Http\Resources;

use App\Models\PostImage;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin PostImage
 */
class PostImageResource extends JsonResource
{
    public static $wrap = null;

    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'url' => $this->url,
            'prompt' => $this->prompt,
            'generation_type' => $this->generation_type->value,
        ];
    }
}
