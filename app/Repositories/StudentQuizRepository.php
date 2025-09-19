<?php

namespace App\Repositories;

use App\Models\QuizClass;
use App\Models\StudentAnswer;
use App\Models\QuestionSets;


class StudentQuizRepository implements StudentQuizRepositoryInterface
{
    /**
     * Get all available quizzes for a student.
     *
     * @param int $studentId
     * @return \Illuminate\Support\Collection
     */
    public function getAvailableQuizzesForStudent($studentId)
    {
        // Example: Get all classes the student is enrolled in, with available quizzes
        return QuizClass::with(['questionSets.questions'])
            ->whereHas('students', function($q) use ($studentId) {
                $q->where('user_id', $studentId);
            })
            ->get();
    }

    /**
     * Submit answers for a quiz.
     *
     * @param int $studentId
     * @param int $questionSetId
     * @param array $answers
     * @return void
     */
    public function submitQuizAnswers($studentId, $questionSetId, $answers)
    {
        foreach ($answers as $questionId => $answer) {
            StudentAnswer::updateOrCreate(
                [
                    'user_id' => $studentId,
                    'question_id' => $questionId,
                    'question_set_id' => $questionSetId,
                ],
                [
                    'answer' => $answer,
                ]
            );
        }
    }
}