<?php

namespace App\Domain\QuizState;

use App\Models\QuestionSet;

final class ClosedState extends AbstractState
{
    public function name(): string { return 'CLOSED'; }

    public function canSeeResults(): bool { return true; }

    public function activate(QuestionSet $qs): void
    {
        $qs->forceFill([
            'state'     => 'ACTIVE',
            'is_active' => true,
        ])->save();
    }

    // If you want “Disable” here to mean “back to Draft”
    public function disable(QuestionSet $qs): void
    {
        $qs->forceFill([
            'state'     => 'DRAFT',
            'is_active' => false,
        ])->save();
    }

    public function archive(QuestionSet $qs): void
    {
        $qs->forceFill([
            'state'     => 'ARCHIVED',
            'is_active' => false,
        ])->save();
    }
}
