<?php

namespace App\Exceptions;

use Exception;

class DuplicatePublishException extends Exception
{
    public static function alreadyPublishing(): self
    {
        return new self('This post is already being published.');
    }

    public static function alreadyPublished(): self
    {
        return new self('This post has already been published to LinkedIn.');
    }
}
