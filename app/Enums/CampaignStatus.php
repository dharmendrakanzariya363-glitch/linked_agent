<?php

namespace App\Enums;

enum CampaignStatus: string
{
    case Draft = 'draft';
    case Active = 'active';
    case Paused = 'paused';
    case Completed = 'completed';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::Active => 'Active',
            self::Paused => 'Paused',
            self::Completed => 'Completed',
        };
    }

    public function canGeneratePosts(): bool
    {
        return $this === self::Active;
    }
}
