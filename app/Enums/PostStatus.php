<?php

namespace App\Enums;

enum PostStatus: string
{
    case Generating = 'generating';
    case Ready = 'ready';
    case Approved = 'approved';
    case Publishing = 'publishing';
    case Published = 'published';
    case Failed = 'failed';

    public function label(): string
    {
        return match ($this) {
            self::Generating => 'Generating',
            self::Ready => 'Ready for review',
            self::Approved => 'Approved',
            self::Publishing => 'Publishing',
            self::Published => 'Published',
            self::Failed => 'Failed',
        };
    }

    public function isTerminal(): bool
    {
        return $this === self::Published;
    }

    public function canEdit(): bool
    {
        return in_array($this, [self::Ready, self::Failed], true);
    }

    public function canApprove(): bool
    {
        return $this === self::Ready;
    }

    public function canRetryGeneration(): bool
    {
        return in_array($this, [self::Ready, self::Failed], true);
    }

    public function canPublish(): bool
    {
        return $this === self::Approved;
    }
}
