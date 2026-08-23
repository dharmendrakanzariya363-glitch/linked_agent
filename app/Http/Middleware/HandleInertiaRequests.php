<?php

namespace App\Http\Middleware;

use App\Http\Resources\NotificationResource;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $user = $request->user();

        return [
            ...parent::share($request),
            'name' => config('app.name'),
            'auth' => [
                'user' => $user ? [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'email_verified_at' => $user->email_verified_at,
                ] : null,
            ],
            'appearance' => $request->cookie('appearance', 'system'),
            'flash' => [
                'success' => $request->session()->get('success'),
                'error' => $request->session()->get('error'),
            ],
            'linkedinConnected' => $user?->hasConnectedLinkedIn() ?? false,
            'unreadNotificationsCount' => $user?->unreadNotifications()->count() ?? 0,
            'recentNotifications' => $user
                ? NotificationResource::collection($user->notifications()->latest()->limit(8)->get())
                : [],
        ];
    }
}
