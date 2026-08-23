<?php

namespace App\Exceptions;

use Exception;

class DuplicatePostException extends Exception
{
    public static function forDate(string $date): self
    {
        return new self("A post already exists for {$date}.");
    }
}
