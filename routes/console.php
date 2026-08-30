<?php

use App\Services\Campaign\CampaignScheduleService;
use App\Services\Campaign\CampaignService;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use Laravel\Ai\Image;

use function Laravel\Ai\agent;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('ai:test {--image : Also test image generation}', function () {
    $provider = (string) config('ai.default');
    $imageProvider = (string) config('ai.default_for_images');
    $key = (string) config("ai.providers.{$provider}.key");

    $this->info("Text provider: {$provider}");
    $this->info("Image provider: {$imageProvider}");

    if ($key === '') {
        $env = $provider === 'openai' ? 'OPENAI_API_KEY' : strtoupper($provider).'_API_KEY';
        $this->error("Missing {$env} in .env");

        return 1;
    }

    $this->comment('Calling text API...');

    try {
        $response = agent(
            instructions: 'Reply with one short word only.',
        )->prompt('Reply with the single word: pong', provider: $provider);

        $text = is_object($response) && isset($response->text)
            ? trim((string) $response->text)
            : trim((string) $response);

        $this->info('Text API: OK');
        $this->line($text === '' ? '(empty response)' : $text);
    } catch (\Throwable $e) {
        $this->error('Text API failed: '.$e->getMessage());

        if ($e->getPrevious()) {
            $this->line($e->getPrevious()->getMessage());
        }

        return 1;
    }

    if (! $this->option('image')) {
        return 0;
    }

    $this->comment('Calling image API...');

    try {
        Image::of('A simple solid blue square, no text')
            ->square()
            ->quality('medium')
            ->timeout(60)
            ->generate(provider: $imageProvider);

        $this->info('Image API: OK');
    } catch (\Throwable $e) {
        $this->error('Image API failed: '.$e->getMessage());

        if ($e->getPrevious()) {
            $this->line($e->getPrevious()->getMessage());
        }

        return 1;
    }

    return 0;
})->purpose('Ping the configured Gemini or OpenAI provider');

Artisan::command('posts:generate-due', function (CampaignScheduleService $scheduler) {
    $count = $scheduler->dispatchDueCampaigns();
    $this->info("Dispatched {$count} generation job(s).");
})->purpose('Dispatch AI generation jobs for due campaigns');

Artisan::command('campaigns:complete-expired', function (CampaignService $campaigns) {
    $count = $campaigns->completeExpired();
    $this->info("Completed {$count} expired campaign(s).");
})->purpose('Mark expired campaigns as completed');

Schedule::command('posts:generate-due')->everyMinute()->withoutOverlapping();
Schedule::command('campaigns:complete-expired')->daily();
