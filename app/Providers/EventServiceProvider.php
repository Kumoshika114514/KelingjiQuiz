<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
     protected $listen = [
        \App\Events\AccountSuspended::class => [
            \App\Listeners\SendAccountSuspendedEmail::class,
            \App\Listeners\RevokeTokensOnSuspension::class,
        ],
    ];
    
    public function boot()
    {
        parent::boot();
    }
}
