<?php

namespace App\Domain\QuizState;

use App\Models\QuestionSet;

final class ArchivedState extends AbstractState
{
    public function name(): string { return 'ARCHIVED'; }

    // Read-only; **allow Restore** via disable()
    public function disable(QuestionSet $qs): void
    {
        // Treat “Disable” as “Restore to Draft”
        $qs->forceFill([
            'state'     => 'DRAFT',
            'is_active' => false,
        ])->save();
    }

    // Everything else stays denied (uses AbstractState::deny()).
}
