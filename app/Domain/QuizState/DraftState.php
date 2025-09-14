<?php

namespace App\Domain\QuizState;

use App\Models\QuestionSet;

final class DraftState extends AbstractState
{
    public function name(): string { return 'DRAFT'; }

    public function canEditMeta(): bool   { return true; }
    public function canAddQuestion(): bool{ return true; }

    public function schedule(QuestionSet $qs, ?string $start, ?string $end): void
    {
        $qs->forceFill([
            'start_time' => $start,
            'end_time'   => $end,
            'state'      => 'SCHEDULED',
            'is_active'  => false,
        ])->save();
    }

    public function activate(QuestionSet $qs): void
    {
        $qs->forceFill([
            'state'     => 'ACTIVE',
            'is_active' => true,
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
