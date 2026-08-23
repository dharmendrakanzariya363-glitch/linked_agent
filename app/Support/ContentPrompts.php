<?php

namespace App\Support;

use App\Models\Campaign;
use App\Models\CampaignTopic;
use App\Models\PostVersion;

class ContentPrompts
{
    public static function systemInstructions(): string
    {
        return <<<'PROMPT'
You are an expert LinkedIn ghostwriter for professional software engineers and technical founders.

Write original LinkedIn posts that:
- sound like a real practitioner, not a brand intern
- open with a specific hook in the first two lines
- teach one clear idea with concrete examples
- stay scannable with short paragraphs
- avoid hype, clickbait, emojis, and generic motivational filler
- never mention that the post was written by AI
- never invent statistics, customer names, or case studies
- keep the body between 800 and 1,600 characters
- include 3 to 5 relevant hashtags without stuffing

Return structured output only.
PROMPT;
    }

    public static function generate(Campaign $campaign, CampaignTopic $topic): string
    {
        $imageLine = $campaign->requiresImage()
            ? 'Also provide an image_prompt for a clean, professional, photorealistic visual that supports the post. No text overlays, no logos, no watermarks.'
            : 'Set image_prompt to a short unused visual idea anyway.';

        return <<<PROMPT
Write today's LinkedIn post.

Campaign: {$campaign->name}
Topic: {$topic->title}
Content type: {$campaign->content_type->label()}
Audience: software engineers, technical leads, and builders.

{$imageLine}

Return:
- description: the full post body without hashtags
- hashtags: an array of 3-5 hashtags including the # symbol
- image_prompt: a detailed visual prompt
PROMPT;
    }

    public static function regenerate(Campaign $campaign, CampaignTopic $topic, PostVersion $current): string
    {
        return <<<PROMPT
Write a completely new LinkedIn post for the same campaign and topic. Do not reuse sentences from the previous version.

Campaign: {$campaign->name}
Topic: {$topic->title}

Previous version (do not copy):
{$current->description}

Return a new description, hashtags, and image_prompt.
PROMPT;
    }

    public static function edit(PostVersion $current, string $instruction): string
    {
        $instruction = trim($instruction);

        return <<<PROMPT
Revise the LinkedIn post using the user's instruction. Keep it professional, specific, and ready to publish. Preserve the core idea unless the instruction asks otherwise.

User instruction:
{$instruction}

Current post:
{$current->description}

Return the revised description, hashtags, and image_prompt.
PROMPT;
    }

    public static function image(Campaign $campaign, CampaignTopic $topic, string $description, string $imagePrompt): string
    {
        return trim(<<<PROMPT
Create a professional LinkedIn post image.

Campaign: {$campaign->name}
Topic: {$topic->title}
Post context: {$description}

Visual direction: {$imagePrompt}

Style: modern, photographic, high-end SaaS aesthetic, natural lighting, no text, no watermarks, no logos, no UI chrome.
PROMPT);
    }

    public static function editImage(string $instruction, string $currentPrompt): string
    {
        return trim(<<<PROMPT
Update this LinkedIn image based on the user's instruction. Keep it professional and photorealistic. No text overlays.

User instruction:
{$instruction}

Previous visual direction:
{$currentPrompt}
PROMPT);
    }
}
