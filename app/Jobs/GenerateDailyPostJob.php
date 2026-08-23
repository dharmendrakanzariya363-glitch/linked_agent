<?php

namespace App\Jobs;

use App\Exceptions\DuplicatePostException;
use App\Models\Campaign;
use App\Services\Post\PostGenerationService;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class GenerateDailyPostJob implements ShouldBeUnique, ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $timeout = 180;

    /** @var list<int> */
    public array $backoff = [30, 90, 180];

    public int $uniqueFor = 3600;

    public function __construct(
        public int $campaignId,
        public string $scheduledFor,
    ) {}

    public function uniqueId(): string
    {
        return $this->campaignId.':'.$this->scheduledFor;
    }

    public function handle(PostGenerationService $generator): void
    {
        $campaign = Campaign::query()->with(['linkedinAccount', 'topics'])->find($this->campaignId);

        if ($campaign === null) {
            return;
        }

        try {
            $generator->generateForDate($campaign, $this->scheduledFor);
        } catch (DuplicatePostException) {
            return;
        }
    }

    public function failed(?\Throwable $exception): void
    {
        Log::error('Daily post generation job failed.', [
            'campaign_id' => $this->campaignId,
            'scheduled_for' => $this->scheduledFor,
            'exception' => $exception instanceof \Throwable ? $exception::class : null,
            'message' => $exception?->getMessage(),
        ]);
    }
}
