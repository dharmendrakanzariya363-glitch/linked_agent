<?php

namespace App\Enums;

enum ImageGenerationType: string
{
    case Generated = 'generated';
    case Regenerated = 'regenerated';
    case AiEdited = 'ai_edited';

    public function label(): string
    {
        return match ($this) {
            self::Generated => 'Generated',
            self::Regenerated => 'Regenerated',
            self::AiEdited => 'AI edited',
        };
    }
}
