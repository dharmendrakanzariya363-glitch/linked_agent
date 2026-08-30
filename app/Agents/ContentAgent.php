<?php

namespace App\Agents;

use App\Support\ContentPrompts;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\HasStructuredOutput;
use Laravel\Ai\Promptable;
use Stringable;

class ContentAgent implements Agent, HasStructuredOutput
{
    use Promptable;

    public function provider(): string
    {
        return (string) config('ai.default');
    }

    public function instructions(): Stringable|string
    {
        return ContentPrompts::systemInstructions();
    }

    /**
     * @return array<string, mixed>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'description' => $schema->string()->min(1)->required(),
            'hashtags' => $schema->array()->items($schema->string())->min(1)->required(),
            'image_prompt' => $schema->string()->min(1)->required(),
        ];
    }
}
