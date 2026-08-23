<?php

namespace App\Enums;

enum PostVersionType: string
{
    case Initial = 'initial';
    case Regenerated = 'regenerated';
    case AiEdited = 'ai_edited';
    case Manual = 'manual';

    public function label(): string
    {
        return match ($this) {
            self::Initial => 'Initial generation',
            self::Regenerated => 'Regenerated',
            self::AiEdited => 'AI edit',
            self::Manual => 'Manual edit',
        };
    }
}
