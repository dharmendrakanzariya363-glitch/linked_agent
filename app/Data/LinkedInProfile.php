<?php

namespace App\Data;

class LinkedInProfile
{
    /**
     * @param  list<string>  $scopes
     */
    public function __construct(
        public string $id,
        public string $name,
        public ?string $email,
        public ?string $headline,
        public ?string $profileUrl,
        public ?string $avatarUrl,
        public string $accessToken,
        public ?string $refreshToken,
        public ?\DateTimeInterface $expiresAt,
        public array $scopes,
    ) {}
}
