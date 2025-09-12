<?php

namespace App\Listeners;

use App\Events\AccountSuspended;
use Illuminate\Support\Facades\Log;

class RevokeTokenOnSuspension
{
    /**
     * Handle the event.
     */
    public function handle(AccountSuspended $event): void
    {
        try {
            // revoke all personal access tokens (Sanctum)
            $event->user->tokens()->delete();

            Log::info('Revoked API tokens on suspension', [
                'user_id' => $event->user->id,
                'until' => $event->until->toDateTimeString(),
            ]);
        } catch (\Throwable $ex) {
            Log::warning('Failed to revoke tokens on suspension: ' . $ex->getMessage(), [
                'user_id' => $event->user->id,
            ]);
        }
    }
}
