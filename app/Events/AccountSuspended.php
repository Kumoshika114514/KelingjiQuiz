<?php

namespace App\Events;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AccountSuspended
{
    use Dispatchable, SerializesModels;

    public User $user;
    public int $minutes;
    public Carbon $until;
    public string $reason;

    public function __construct(User $user, int $minutes, Carbon $until, string $reason = 'multiple failed login attempts')
    {
        $this->user   = $user;
        $this->minutes = $minutes;
        $this->until   = $until;
        $this->reason  = $reason;
    }
}
