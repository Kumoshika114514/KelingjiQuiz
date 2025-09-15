<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Response;

use App\Facades\Statistic;
use App\Models\QuizClass;
use Illuminate\Http\Request;

class QuizClassController extends Controller
{
    public function create()
    {
        return view('teacher.create');
    }

    public function store(Request $request)
    {
        $code = $this->generateUniqueClassCode();

        QuizClass::create([
            'name' => $request->input('name'),
            'description' => $request->input('description'),
            'class_code' => $code,
            'user_id' => Auth::id(),
        ]);

        return Redirect::route('teacher.dashboard')->with('success', 'Class created successfully!');
    }
    private function generateUniqueClassCode($length = 10)
    {
        $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*';

        do {
            $code = '';
            for ($i = 0; $i < $length; $i++) {
                $code .= $characters[random_int(0, strlen($characters) - 1)];
            }
        } while (QuizClass::where('class_code', $code)->exists());

        return $code;
    }

    // quiz class show function (not used, switched to API approach)
    public function show($id)
    {
        $quizClass = QuizClass::with(['students', 'questionSets'])->findOrFail($id);

        if ($quizClass->teacher_id != Auth::id()) {
            abort(403, 'Unauthorized');
        }
        return view('teacher.quizclass', compact('quizClass'));
    }

    // API for fetching all quizclasses
    public function loadQuizClassDetail($id)
    {
        $quizClass = QuizClass::findOrFail($id);

        // only allow the owner (teacher)
        if ($quizClass->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return Response::json([
            'quizClass' => $quizClass,
        ], 200);
    }

    //load all question sets that belongs to the quiz class with id = $id
    public function loadClassQuestionSets($id)
    {
        $quizClass = QuizClass::with('questionSets')->findOrFail($id);
        $totalQuestionSets = Statistic::totalQuestionSetInClass($id);
        $questionSets = $quizClass->questionSets->map(function ($set) {
            $set->highest_score = Statistic::getHighestScoreInQuiz($set->id);
            return $set;
        });


        if ($quizClass->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return Response::json([
            'totalQuestionSets' => $totalQuestionSets,
            'questionSets' => $questionSets,
        ], 200);
    }

    // load all students that belongs to the quiz class with id = $id
    public function loadClassStudents($id)
    {
        $quizClass = QuizClass::with([
            'students' => function ($query) {
                // use users.xxx otherwise will error because 
                // both users and student_classes tables have a column named id
                $query->select('users.id', 'users.name', 'users.email'); 
            }
        ])->findOrFail($id);

        $totalStudents = Statistic::totalStudentsInClass($id);

        if ($quizClass->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return Response::json([
            'totalStudents' => $totalStudents,
            'students' => $quizClass->students,
        ], 200);
    }

    // redirect to edit page
    public function edit($id)
    {
        $quizClass = QuizClass::findOrFail($id);
        return view('teacher.editquizclass', compact('quizClass'));

    }

    // update the quiz class detail
    // when complete, go back to quiz class page with success message
    public function update(Request $request, $id)
    {
        $quizClass = QuizClass::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string|max:255'
        ]);

        $quizClass->update(['name' => $request->name, 'description' => $request->description]);

        return Redirect::route('teacher.quizclass', $quizClass->id)
            ->with('success', 'Class updated successfully.');
    }
}
