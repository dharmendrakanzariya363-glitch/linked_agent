<?php

use App\Http\Controllers\AppearanceController;
use App\Http\Controllers\CampaignController;
use App\Http\Controllers\CampaignTopicController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LinkedInController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\Settings\AppearanceController as AppearanceSettingsController;
use App\Http\Controllers\Settings\PasswordController;
use App\Http\Controllers\Settings\ProfileController;
use App\Http\Controllers\TodayPostController;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', DashboardController::class)->name('dashboard');

    Route::get('linkedin', [LinkedInController::class, 'index'])->name('linkedin.index');
    Route::get('linkedin/connect', [LinkedInController::class, 'connect'])->name('linkedin.connect');
    Route::delete('linkedin/{linkedinAccount}', [LinkedInController::class, 'disconnect'])->name('linkedin.disconnect');

    Route::resource('campaigns', CampaignController::class);
    Route::post('campaigns/{campaign}/activate', [CampaignController::class, 'activate'])->name('campaigns.activate');
    Route::post('campaigns/{campaign}/pause', [CampaignController::class, 'pause'])->name('campaigns.pause');

    Route::post('campaigns/{campaign}/topics', [CampaignTopicController::class, 'store'])->name('campaigns.topics.store');
    Route::put('campaigns/{campaign}/topics/reorder', [CampaignTopicController::class, 'reorder'])->name('campaigns.topics.reorder');
    Route::put('topics/{topic}', [CampaignTopicController::class, 'update'])->name('topics.update');
    Route::delete('topics/{topic}', [CampaignTopicController::class, 'destroy'])->name('topics.destroy');

    Route::get('posts/today', TodayPostController::class)->name('posts.today');
    Route::get('posts', [PostController::class, 'index'])->name('posts.index');
    Route::get('posts/{post}', [PostController::class, 'show'])->name('posts.show');
    Route::put('posts/{post}', [PostController::class, 'update'])->name('posts.update');
    Route::post('posts/{post}/regenerate', [PostController::class, 'regenerate'])->middleware('throttle:ai')->name('posts.regenerate');
    Route::post('posts/{post}/regenerate-image', [PostController::class, 'regenerateImage'])->middleware('throttle:ai')->name('posts.regenerate-image');
    Route::post('posts/{post}/ai-edit', [PostController::class, 'aiEdit'])->middleware('throttle:ai')->name('posts.ai-edit');
    Route::post('posts/{post}/ai-edit-image', [PostController::class, 'aiEditImage'])->middleware('throttle:ai')->name('posts.ai-edit-image');
    Route::post('posts/{post}/approve', [PostController::class, 'approve'])->middleware('throttle:publish')->name('posts.approve');
    Route::post('posts/{post}/retry', [PostController::class, 'retry'])->middleware('throttle:publish')->name('posts.retry');

    Route::get('notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('notifications/{notification}/read', [NotificationController::class, 'read'])->name('notifications.read');
    Route::post('notifications/read-all', [NotificationController::class, 'readAll'])->name('notifications.read-all');

    Route::get('settings/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::get('settings/password', [PasswordController::class, 'edit'])->name('password.edit');
    Route::get('settings/appearance', [AppearanceSettingsController::class, 'edit'])->name('appearance.edit');
    Route::post('settings/appearance', [AppearanceController::class, 'update'])->name('appearance.update');
});

Route::middleware('auth')->group(function () {
    Route::get('linkedin/callback', [LinkedInController::class, 'callback'])->name('linkedin.callback');
});
