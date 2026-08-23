<?php

use App\Services\Campaign\CampaignScheduleService;
use App\Services\Campaign\CampaignService;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

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
