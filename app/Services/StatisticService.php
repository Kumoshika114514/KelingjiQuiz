<?php
namespace App\Services;

use Illuminate\Support\Facades\DB;

use App\Models\QuestionSet;
use App\Models\User;
use App\Models\QuizClass;
use App\Models\StudentClass;

/**
 * Summary of StatisticService
 * Provide statistics for the data like count students in a class
 * Made by Chan Huan Lin
 */
class StatisticService
{
    public function totalStudents()
    {
        return User::where('role', 'student')->count();
    }

    public function totalStudentsInClass($classId)
    {
        return StudentClass::where('class_id', $classId)->count();
    }
    public function totalClassesByTeacher($teacherId)
    {
        return QuizClass::where('user_id', $teacherId)->count();
    }

    public function totalQuestionSetInClass($classId)
    {
        return QuestionSet::where('class_id', $classId)->count();
    }

    public function getHighestScoreInQuiz($quizId){
        /**
         * Get the highest score from the question sets
         * Count the answer from studentanswers table
         * where the answer is correct (equal to answer_a in questions table)
         * which the questions belongs to the question sets
         */
         return DB::table('student_answers')
        ->join('questions', 'student_answers.question_id', '=', 'questions.id')
        ->where('questions.question_set_id', $quizId)
        ->whereColumn('student_answers.answer', 'questions.answer_a')
        ->select('student_answers.user_id', DB::raw('COUNT(*) as score'))
        ->groupBy('student_answers.user_id')
        ->orderByDesc('score')
        ->limit(1)
        ->value('score');
    }
}
