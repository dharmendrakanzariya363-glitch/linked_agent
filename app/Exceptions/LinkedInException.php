<?php

namespace App\Exceptions;

use Exception;

class LinkedInException extends Exception
{
    public static function oauthDenied(): self
    {
        return new self('LinkedIn authorization was cancelled or denied.');
    }

    public static function invalidState(): self
    {
        return new self('The LinkedIn authorization session expired. Please try connecting again.');
    }

    public static function tokenExchangeFailed(): self
    {
        return new self('We could not complete LinkedIn authorization. Please try again.');
    }

    public static function notConnected(): self
    {
        return new self('A LinkedIn account must be connected before this action can continue.');
    }

    public static function tokenExpired(): self
    {
        return new self('The LinkedIn connection has expired. Please reconnect your account.');
    }

    public static function publishFailed(string $userMessage = 'LinkedIn could not publish this post. Please try again.'): self
    {
        return new self($userMessage);
    }
}
