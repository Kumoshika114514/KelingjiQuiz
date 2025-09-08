<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Login;

class SavePreferencesOnLogin
{
    /**
     * Handle the event.
     */
    public function handle(Login $event): void
    {
        $request = request();

        $font = $request->input('font_size');
        $allowed = ['sm','md','lg'];
        if (in_array($font, $allowed, true)) {
            session(['font_size' => $font]);
        }

        $theme = $request->input('theme', $request->cookie('theme'));
        if ($theme === 'dark' || $theme === 'light') {
            session(['theme' => $theme]);
        }
    }
}
