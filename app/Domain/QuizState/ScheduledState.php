<?php

namespace App\Domain\QuizState;

use App\Models\QuestionSet;

final class ScheduledState extends AbstractState
{
    public function name(): string { return 'SCHEDULED'; }

    public function canEditMeta(): bool   { return true; }
    public function canAddQuestion(): bool{ return true; }

    public function activate(QuestionSet $qs): void
    {
        $qs->forceFill([
            'state'     => 'ACTIVE',
            'is_active' => true,
        ])->save();
    }

    // “Back to draft”
    public function disable(QuestionSet $qs): void
    {
        $qs->forceFill([
            'state'     => 'DRAFT',
            'is_active' => false,
        ])->save();
    }

    public function close(QuestionSet $qs): void
    {
        $qs->forceFill([
            'state'     => 'CLOSED',
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
