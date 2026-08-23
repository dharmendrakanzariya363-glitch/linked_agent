<?php

namespace App\Exceptions;

use Exception;

class AIGenerationException extends Exception
{
    public static function failed(string $operation = 'generate content'): self
    {
        return new self("AI was unable to {$operation}. Please try again.");
    }
}
