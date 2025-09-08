<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\StatisticService;

class StatisticServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton('statisticservice', function ($app) {
            return new StatisticService();
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
