<?php

namespace App\Data;

class GeneratedCopy
{
    /**
     * @param  list<string>  $hashtags
     */
    public function __construct(
        public string $description,
        public array $hashtags,
        public string $imagePrompt,
    ) {}

    public function body(): string
    {
        $hashtags = implode(' ', $this->hashtags);

        return trim($this->description.($hashtags !== '' ? "\n\n".$hashtags : ''));
    }
}
