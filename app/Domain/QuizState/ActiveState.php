<?php

namespace App\Domain\QuizState;

use App\Models\QuestionSet;

final class ActiveState extends AbstractState
{
    public function name(): string { return 'ACTIVE'; }

    public function canAttempt(): bool { return true; }

    // Disable == close
    public function disable(QuestionSet $qs): void
    {
        $this->close($qs);
    }

    public function close(QuestionSet $qs): void
    {
        $qs->forceFill([
            'state'     => 'CLOSED',
            'is_active' => false,
        ])->save();
    }
}
