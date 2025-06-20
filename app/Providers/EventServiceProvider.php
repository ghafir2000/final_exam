<?php

namespace App\Providers;

use App\Models\Breed;
use App\Models\Message;
use App\Events\NewChatMessage;
use App\Events\NewNotification;
use App\Observers\BreedObserver;
use App\Observers\MessageObserver;
use Illuminate\Support\Facades\Event;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $observers = [
        Message::class => [MessageObserver::class],
        Breed::class => [BreedObserver::class],
    ];
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
            NewChatMessage::class,
            NewNotification::class
        ],

    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        //
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
