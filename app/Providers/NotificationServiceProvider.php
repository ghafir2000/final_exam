<?php

namespace App\Providers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class NotificationServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        View::composer(['web.layout', 'web.navbar'], function ($view) {
            if (Auth::check()) {
                $unreadNotifications = Auth::user()->unreadNotifications()->take(5)->get(); // Get latest 5 unread
                $unreadNotificationsCount = Auth::user()->unreadNotifications()->count();
                $view->with(compact('unreadNotifications', 'unreadNotificationsCount'));
            } else {
                $view->with(['unreadNotifications' => collect(), 'unreadNotificationsCount' => 0]);
            }
        });
    }
}
