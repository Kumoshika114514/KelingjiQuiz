{{-- filepath: c:\xampp\htdocs\KelingjiQuiz\resources\views\student\quizzes\summary.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Quiz Summary: {{ $questionSet->topic }}
        </h2>
    </x-slot>
    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow rounded-lg p-6 mb-6">
                {{-- Score Section --}}
                @php
                    $total = $questionSet->questions->count();
                    $score = 0;
                    foreach($questionSet->questions as $question) {
                        $studentAnswer = $answers[$question->id]->answer ?? null;
                        if ($question->correct_choice && $studentAnswer) {
                            if (strtolower($studentAnswer) == strtolower($question->correct_choice)) $score++;
                        } elseif ($question->correct_text && $studentAnswer) {
                            if (trim(strtolower($studentAnswer)) == trim(strtolower($question->correct_text))) $score++;
                        } elseif (!is_null($question->correct_bool) && !is_null($studentAnswer)) {
                            if (strtolower($studentAnswer) == ($question->correct_bool ? 'true' : 'false')) $score++;
                        }
                    }
                @endphp
                <div class="mb-6 flex items-center justify-between">
                    <div>
                        <span class="text-lg font-bold text-blue-700">Your Score: {{ $score }} / {{ $total }}</span>
                    </div>
                    <a href="{{ route('student.viewClass', $questionSet->class_id) }}"
                       class="inline-block bg-gray-200 hover:bg-gray-300 text-gray-800 font-semibold py-2 px-4 rounded">
                        ← Back to Class
                    </a>
                </div>
                <ul>
                    @foreach($questionSet->questions as $question)
                        @php
                            // Determine correct answer display
                            if ($question->correct_text) {
                                $correctAnswer = $question->correct_text;
                            } elseif (!is_null($question->correct_bool)) {
                                $correctAnswer = $question->correct_bool ? 'True' : 'False';
                            } elseif ($question->correct_choice) {
                                $correctAnswer = $question->correct_choice;
                            } else {
                                $correctAnswer = '-';
                            }

                            // Student's answer
                            $studentAnswer = $answers[$question->id]->answer ?? null;

                            // Check correctness
                            $isCorrect = false;
                            if ($question->correct_choice && $studentAnswer) {
                                $isCorrect = strtolower($studentAnswer) == strtolower($question->correct_choice);
                            } elseif ($question->correct_text && $studentAnswer) {
                                $isCorrect = trim(strtolower($studentAnswer)) == trim(strtolower($question->correct_text));
                            } elseif (!is_null($question->correct_bool) && !is_null($studentAnswer)) {
                                $isCorrect = (strtolower($studentAnswer) == ($question->correct_bool ? 'true' : 'false'));
                            }
                        @endphp
                        <li class="mb-6 border-b pb-4">
                            <div class="font-semibold mb-1">Q: {{ $question->text ?? '[No question text]' }}</div>
                            <div>
                                <span class="font-semibold">Your Answer:</span>
                                {{ $studentAnswer ?? '-' }}
                            </div>
                            <div>
                                <span class="font-semibold">Correct Answer:</span>
                                {{ $correctAnswer }}
                            </div>
                            @if($studentAnswer !== null)
                                @if($isCorrect)
                                    <span class="text-green-600 font-bold">Correct</span>
                                @else
                                    <span class="text-red-600 font-bold">Incorrect</span>
                                @endif
                            @endif
                            @if(isset($answers[$question->id]) && $answers[$question->id]->note)
                                <div class="text-sm text-gray-500 mt-1">Feedback: {{ $answers[$question->id]->note }}</div>
                            @endif
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
</x-app-layout>