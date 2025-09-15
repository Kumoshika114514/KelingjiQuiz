<?php

namespace App\Domain\QuizState;

use App\Models\QuestionSet;

interface QuizState
{
    public function name(): string;

    // permissions
    public function canEditMeta(): bool;
    public function canAddQuestion(): bool;
    public function canAttempt(): bool;
    public function canSeeResults(): bool;

    // transitions
    public function schedule(QuestionSet $qs, ?string $start, ?string $end): void;
    public function activate(QuestionSet $qs): void;
    public function disable(QuestionSet $qs): void;
    public function close(QuestionSet $qs): void;
    public function archive(QuestionSet $qs): void;
}
