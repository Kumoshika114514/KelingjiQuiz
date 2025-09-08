<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;

use Illuminate\Http\Request;
use App\Models\QuizClass;
use App\Models\StudentClass;

class StudentClassController extends Controller
{
    public function create()
    {
        return view("join");
    }

    public function store(Request $request)
    {
        $request->validate([
            'class_code' => 'required|string'
        ]);

        $quizClass = QuizClass::where('class_code', $request->class_code)->first();

        if (!$quizClass) {
            return back()->with('error', 'Class code not found.');
        }

        StudentClass::firstOrCreate([
            'student_id' => Auth::id(),
            'class_id' => $quizClass->id,
        ]);

        return Redirect::route('dashboard')->with('success', 'Successfully joined class.');
    }

    public function destroy($classId, $studentId)
    {
        $studentClass = StudentClass::where('student_id', $studentId)
            ->where('class_id', $classId)
            ->first();

        if ($studentClass) {
            $studentClass->delete();
            return Redirect::back()->with('success', 'Student removed from the class.');
        }

        return Redirect::back()->with('error', 'Student not found in this class.');
    }

}
