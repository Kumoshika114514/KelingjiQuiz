<?php

namespace App\Http\Controllers;

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
            'teacher_id' => auth()->id(),
        ]);

        return redirect()->route('teacher.dashboard')->with('success', 'Class created successfully!');
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

    public function show($id)
    {
        $quizClass = QuizClass::with(['students', 'questionSets'])->findOrFail($id);

        if ($quizClass->teacher_id != auth()->id()) {
            abort(403, 'Unauthorized');
        }
        return view('teacher.quizclass', compact('quizClass'));
    }

    public function edit($id)
    {
        $quizClass = QuizClass::findOrFail($id);
        return view('teacher.editquizclass', compact('quizClass'));

    }
    public function update(Request $request, $id)
    {
        $quizClass = QuizClass::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string|max:255'
        ]);

        $quizClass->update(['name'=> $request->name, 'description'=> $request->description]);

        return redirect()->route('teacher.quizclass', $quizClass->id)
            ->with('success', 'Class updated successfully.');
    }
}
