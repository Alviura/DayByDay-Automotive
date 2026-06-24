<?php

namespace App\Providers;

use App\Services\NavigationBadgeService;
use App\Services\NotificationInboxService;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::composer('layouts.app', function ($view) {
            if (! auth()->check()) {
                return;
            }

            $user = auth()->user();
            $inbox = app(NotificationInboxService::class);

            $view->with('navBadges', app(NavigationBadgeService::class)->forUser($user));

            if ($user->can('notifications.view')) {
                $view->with('recentNotifications', $inbox->recent($user, 5));
                $view->with('unreadNotificationCount', $inbox->unreadCount($user));
            } else {
                $view->with('recentNotifications', collect());
                $view->with('unreadNotificationCount', 0);
            }
        });
    }
}
