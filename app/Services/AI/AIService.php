<?php

namespace App\Services\AI;

use App\Agents\ContentAgent;
use App\Data\GeneratedCopy;
use App\Enums\ImageGenerationType;
use App\Exceptions\AIGenerationException;
use App\Models\Campaign;
use App\Models\CampaignTopic;
use App\Models\Post;
use App\Models\PostImage;
use App\Models\PostVersion;
use App\Support\ContentPrompts;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Laravel\Ai\Files\Image as ImageFile;
use Laravel\Ai\Image;
use Throwable;

class AIService
{
    public function generateCopy(Campaign $campaign, CampaignTopic $topic): GeneratedCopy
    {
        return $this->promptCopy(ContentPrompts::generate($campaign, $topic), 'generate a LinkedIn post');
    }

    public function regenerateCopy(Campaign $campaign, CampaignTopic $topic, PostVersion $current): GeneratedCopy
    {
        return $this->promptCopy(ContentPrompts::regenerate($campaign, $topic, $current), 'regenerate a LinkedIn post');
    }

    public function editCopy(PostVersion $current, string $instruction): GeneratedCopy
    {
        return $this->promptCopy(ContentPrompts::edit($current, $instruction), 'edit a LinkedIn post');
    }

    public function generateImage(Campaign $campaign, CampaignTopic $topic, string $description, string $imagePrompt, Post $post, PostVersion $version, ImageGenerationType $type): PostImage
    {
        $prompt = ContentPrompts::image($campaign, $topic, $description, $imagePrompt);

        return $this->storeGeneratedImage($prompt, $post, $version, $type, null);
    }

    public function regenerateImage(Campaign $campaign, CampaignTopic $topic, PostVersion $current, Post $post, PostVersion $version): PostImage
    {
        $previousPrompt = $current->image?->prompt ?: 'A professional visual related to '.$topic->title;

        return $this->generateImage($campaign, $topic, $current->description, $previousPrompt.' Create a distinct new composition.', $post, $version, ImageGenerationType::Regenerated);
    }

    public function editImage(PostVersion $current, string $instruction, Post $post, PostVersion $version): PostImage
    {
        $previousPrompt = $current->image?->prompt ?: 'A professional LinkedIn visual';
        $prompt = ContentPrompts::editImage($instruction, $previousPrompt);
        $attachment = $current->image
            ? [ImageFile::fromStorage($current->image->path, $current->image->disk)]
            : [];

        return $this->storeGeneratedImage($prompt, $post, $version, ImageGenerationType::AiEdited, $attachment);
    }

    private function promptCopy(string $prompt, string $operation): GeneratedCopy
    {
        try {
            $response = (new ContentAgent)->prompt($prompt);

            /** @var array<string, mixed> $payload */
            $payload = is_array($response) ? $response : (array) $response;

            return new GeneratedCopy(
                description: trim((string) ($payload['description'] ?? '')),
                hashtags: $this->normalizeHashtags($payload['hashtags'] ?? []),
                imagePrompt: trim((string) ($payload['image_prompt'] ?? '')),
            );
        } catch (Throwable $e) {
            Log::error('AI copy generation failed.', [
                'operation' => $operation,
                'exception' => $e::class,
                'message' => $e->getMessage(),
            ]);

            throw AIGenerationException::failed($operation);
        }
    }

    /**
     * @param  array<int, mixed>|string  $hashtags
     * @return list<string>
     */
    private function normalizeHashtags(array|string $hashtags): array
    {
        if (is_string($hashtags)) {
            $hashtags = preg_split('/[\s,]+/', $hashtags) ?: [];
        }

        $normalized = [];

        foreach ($hashtags as $tag) {
            $value = trim((string) $tag);

            if ($value === '') {
                continue;
            }

            if (! str_starts_with($value, '#')) {
                $value = '#'.$value;
            }

            $normalized[] = $value;
        }

        return array_values(array_unique($normalized));
    }

    /**
     * @param  array<int, ImageFile>  $attachments
     */
    private function storeGeneratedImage(string $prompt, Post $post, PostVersion $version, ImageGenerationType $type, ?array $attachments): PostImage
    {
        $disk = (string) config('content.media_disk', 'public');

        try {
            $pending = Image::of($prompt)->square()->quality('medium')->timeout(120);

            if ($attachments) {
                $pending->attachments($attachments);
            }

            $image = $pending->generate(provider: (string) config('ai.default_for_images'));
            $filename = 'v'.$version->id.'.png';
            $directory = 'posts/'.$post->id;
            $path = $image->storePubliclyAs($directory, $filename, $disk);

            if (! is_string($path) || $path === '') {
                $path = $directory.'/'.$filename;
            }

            return PostImage::query()->create([
                'user_id' => $post->user_id,
                'post_id' => $post->id,
                'post_version_id' => $version->id,
                'disk' => $disk,
                'path' => $path,
                'prompt' => $prompt,
                'generation_type' => $type,
            ]);
        } catch (AIGenerationException $e) {
            throw $e;
        } catch (Throwable $e) {
            Log::error('AI image generation failed.', [
                'post_id' => $post->id,
                'exception' => $e::class,
                'message' => $e->getMessage(),
            ]);

            throw AIGenerationException::failed('generate an image');
        }
    }

    public function deleteStoredImage(PostImage $image): void
    {
        Storage::disk($image->disk)->delete($image->path);
    }
}
