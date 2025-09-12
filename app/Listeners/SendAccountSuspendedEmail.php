<?php

namespace App\Listeners;

use App\Events\AccountSuspended;
use App\Http\Mail\AccountSuspended as AccountSuspendedMailable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;

class SendAccountSuspendedEmail implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Handle the event.
     */
    public function handle(AccountSuspended $event): void
    {
        // Use the existing mailable; signature: __construct(string $userName, int $durationMinutes, string $reason = ...)
        Mail::to($event->user->email)
            ->send(new AccountSuspendedMailable(
                $event->user->name ?? $event->user->email,
                $event->minutes,
                $event->reason
            ));
    }
}
