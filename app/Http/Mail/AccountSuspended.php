<?php

namespace App\Http\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AccountSuspended extends Mailable
{
    use Queueable, SerializesModels;

    public $userName;
    public $durationMinutes;
    public $reason;

    /**
     * Create a new message instance.
     */
    public function __construct(string $userName, int $durationMinutes, string $reason = 'multiple failed login attempts')
    {
        $this->userName = $userName;
        $this->durationMinutes = $durationMinutes;
        $this->reason = $reason;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this->subject('Your account has been temporarily suspended')
                    ->markdown('emails.account_suspended')
                    ->with([
                        'userName' => $this->userName,
                        'durationMinutes' => $this->durationMinutes,
                        'reason' => $this->reason,
                    ]);
    }
}
