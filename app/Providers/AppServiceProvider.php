<?php

namespace App\Providers;

use App\Events\PostGenerated;
use App\Listeners\SendPostReadyNotification;
use Carbon\CarbonImmutable;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        $this->configureDefaults();
        $this->configureRateLimiting();

        Event::listen(PostGenerated::class, SendPostReadyNotification::class);
    }

    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null,
        );
    }

    protected function configureRateLimiting(): void
    {
        RateLimiter::for('ai', function (Request $request) {
            return Limit::perMinute((int) config('content.ai.rate_limit_per_minute', 6))
                ->by((string) ($request->user()?->id ?: $request->ip()));
        });

        RateLimiter::for('publish', function (Request $request) {
            return Limit::perMinute((int) config('content.publish.rate_limit_per_minute', 3))
                ->by((string) ($request->user()?->id ?: $request->ip()));
        });
    }
}
