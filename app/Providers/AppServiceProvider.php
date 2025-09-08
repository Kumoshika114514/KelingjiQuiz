<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Event;
use Illuminate\Auth\Events\Login;
use App\Listeners\SavePreferencesOnLogin;
use Illuminate\Support\Facades\View;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton('statisticservice', function () {
            return new \App\Services\StatisticService();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot()
    {
        // register login listener
    Event::listen(Login::class, [SavePreferencesOnLogin::class, 'handle']);

    // share safe defaults so Blade never breaks
    View::share('theme', session('theme', 'light'));
    View::share('font_size', session('font_size', 'md'));
    }
}
