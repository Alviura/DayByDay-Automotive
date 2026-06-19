<?php

namespace App\Providers;

use App\Services\ApprovalService;
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
            if (auth()->check() && auth()->user()->can('approvals.act')) {
                $view->with('pendingApprovalCount', app(ApprovalService::class)->pendingCountFor(auth()->user()));
            }
        });
    }
}
