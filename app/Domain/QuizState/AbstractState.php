<?php

namespace App\Domain\QuizState;

use App\Models\QuestionSet;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

abstract class AbstractState implements QuizState
{
    protected function deny(): never { throw new AccessDeniedHttpException('Action not allowed in this state.'); }

    // default perms
    public function canEditMeta(): bool { return false; }
    public function canAddQuestion(): bool { return false; }
    public function canAttempt(): bool { return false; }
    public function canSeeResults(): bool { return false; }

    // default transitions
    public function schedule(QuestionSet $qs, ?string $s, ?string $e): void { $this->deny(); }
    public function activate(QuestionSet $qs): void { $this->deny(); }
    public function disable(QuestionSet $qs): void { $this->deny(); }
    public function close(QuestionSet $qs): void { $this->deny(); }
    public function archive(QuestionSet $qs): void { $this->deny(); }
}
