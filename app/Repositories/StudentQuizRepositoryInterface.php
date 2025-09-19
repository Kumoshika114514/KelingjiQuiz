@ -0,0 +1,24 @@
<?php

namespace App\Repositories;

interface StudentQuizRepositoryInterface
{
    /**
     * Get all available quizzes for a student.
     *
     * @param int $studentId
     * @return \Illuminate\Support\Collection
     */
    public function getAvailableQuizzesForStudent($studentId);

    /**
     * Submit answers for a quiz.
     *
     * @param int $studentId
     * @param int $questionSetId
     * @param array $answers
     * @return void
     */
    public function submitQuizAnswers($studentId, $questionSetId, $answers);
}