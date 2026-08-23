<?php

namespace App\Enums;

enum ContentType: string
{
    case Description = 'description';
    case DescriptionImage = 'description_image';

    public function label(): string
    {
        return match ($this) {
            self::Description => 'Description only',
            self::DescriptionImage => 'Description + image',
        };
    }

    public function includesImage(): bool
    {
        return $this === self::DescriptionImage;
    }
}
