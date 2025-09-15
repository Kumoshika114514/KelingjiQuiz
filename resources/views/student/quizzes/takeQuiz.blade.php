{{-- filepath: c:\xampp\htdocs\KelingjiQuiz\resources\views\student\quizzes\takeQuiz.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ $questionSet->topic }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Quiz Description Section -->
            <div class="bg-white shadow-sm rounded-lg p-6 mb-6">
                <h3 class="font-semibold text-lg">Description:</h3>
                <p>{{ $questionSet->description ?? 'No description.' }}</p>
                @if(isset($questionSet->time_limit))
                    <p>Time Limit: {{ $questionSet->time_limit }} minutes</p>
                @endif
            </div>

            <!-- Questions Section -->
            <form method="POST" action="{{ route('student.quizzes.submit', $questionSet->id) }}">
                @csrf
                <h3 class="font-semibold text-lg mb-4">Questions:</h3>
                @forelse($questionSet->questions as $index => $question)
                    <div class="mb-6 p-4 bg-gray-50 rounded">
                        <div class="mb-2">
                            <strong>{{ $index + 1 }}.</strong>
                            {{ $question->text ?? $question->text ?? $question->content ?? $question->question ?? '[No question text found]' }}
                        </div>
                        @if(!empty($question->answer_a) && !empty($question->answer_b))
                            <!-- Multiple Choice -->
                            <div class="ml-4">
                                <div>
                                    <label>
                                        <input type="radio" name="answers[{{ $question->id }}]" value="A">
                                        {{ $question->answer_a }}
                                    </label>
                                </div>
                                <div>
                                    <label>
                                        <input type="radio" name="answers[{{ $question->id }}]" value="B">
                                        {{ $question->answer_b }}
                                    </label>
                                </div>
                                @if(!empty($question->answer_c))
                                    <div>
                                        <label>
                                            <input type="radio" name="answers[{{ $question->id }}]" value="C">
                                            {{ $question->answer_c }}
                                        </label>
                                    </div>
                                @endif
                                @if(!empty($question->answer_d))
                                    <div>
                                        <label>
                                            <input type="radio" name="answers[{{ $question->id }}]" value="D">
                                            {{ $question->answer_d }}
                                        </label>
                                    </div>
                                @endif
                                @error("answers.$question->id")
                                    <div class="text-red-600 text-sm mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                        @elseif(isset($question->correct_bool))
                            <!-- True/False -->
                            <div class="ml-4">
                                <div>
                                    <label>
                                        <input type="radio" name="answers[{{ $question->id }}]" value="1">
                                        True
                                    </label>
                                </div>
                                <div>
                                    <label>
                                        <input type="radio" name="answers[{{ $question->id }}]" value="0">
                                        False
                                    </label>
                                </div>
                                @error("answers.$question->id")
                                    <div class="text-red-600 text-sm mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                        @else
                            <!-- Short Answer -->
                            <input type="text" name="answers[{{ $question->id }}]" class="border rounded px-2 py-1 w-full"
                                placeholder="Your answer">
                            @error("answers.$question->id")
                                <div class="text-red-600 text-sm mt-1">{{ $message }}</div>
                            @enderror
                        @endif
                    </div>
                @empty
                    <p>No questions found for this Question Set.</p>
                @endforelse
                <button type="submit" class="btn btn-primary">Submit Quiz</button>
            </form>
        </div>
    </div>
</x-app-layout>