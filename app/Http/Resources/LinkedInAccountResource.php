<?php

namespace App\Http\Resources;

use App\Models\LinkedInAccount;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin LinkedInAccount
 */
class LinkedInAccountResource extends JsonResource
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
            'email' => $this->email,
            'headline' => $this->headline,
            'profile_url' => $this->profile_url,
            'avatar_url' => $this->avatar_url,
            'connected' => $this->isConnected(),
            'token_expires_at' => $this->token_expires_at?->toIso8601String(),
        ];
    }
}
