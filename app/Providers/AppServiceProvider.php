<?php

namespace App\Providers;

use App\Models\Breed;
use App\Observers\BreedObserver;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind('path.public', function() {
            return base_path();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Breed::observe(BreedObserver::class);
        Paginator::useBootstrapFive();
    }
}
