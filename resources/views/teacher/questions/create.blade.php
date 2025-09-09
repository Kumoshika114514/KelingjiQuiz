<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-100 leading-tight">
            Add Question — {{ $questionSet->topic }} ({{ strtoupper($questionSet->question_type) }})
        </h2>
    </x-slot>

    @php
        // Normalize the question-set type once
        $qt = strtoupper($questionSet->question_type);  // MCQ | TRUE_FALSE | SHORT_ANSWER
        // Map to the values your controller accepts
        $hiddenType = match ($qt) {
            'MCQ' => 'MCQ',
            'TRUE_FALSE' => 'TRUE_FALSE',
            default => 'SUBJECTIVE', // SHORT_ANSWER -> SUBJECTIVE
        };
    @endphp

    <div class="py-6">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="mb-4">
                <a href="{{ route('teacher.questions.index', [$quizClass->id, $questionSet->id]) }}"
                   class="text-sm text-gray-600 hover:underline">← Back to Questions</a>
            </div>

            <div class="bg-white dark:bg-gray-900 shadow sm:rounded-lg">
                <form class="p-6 space-y-6"
                      action="{{ route('teacher.questions.store', [$quizClass->id, $questionSet->id]) }}"
                      method="POST">
                    @csrf

                    {{-- Ensure the server receives type --}}
                    <input type="hidden" name="type" value="{{ $hiddenType }}">

                    @if ($errors->any())
                        <div class="rounded-md bg-red-50 p-4 text-red-700">
                            <ul class="list-disc pl-5">
                                @foreach ($errors->all() as $e)
                                    <li>{{ $e }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div>
                        <label class="block text-sm font-medium mb-1">Question Text</label>
                        <textarea name="text" rows="4" class="w-full rounded border-gray-300 dark:bg-gray-800" required>{{ old('text') }}</textarea>
                    </div>

                    <div class="grid grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium mb-1">Points</label>
                            <input type="number" name="points" min="0" value="{{ old('points', 1) }}"
                                   class="w-full rounded border-gray-300 dark:bg-gray-800" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Order (optional)</label>
                            <input type="number" name="order" min="0" value="{{ old('order') }}"
                                   class="w-full rounded border-gray-300 dark:bg-gray-800">
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Time Limit (sec, optional)</label>
                            <input type="number" name="time_limit_sec" min="5" value="{{ old('time_limit_sec') }}"
                                   class="w-full rounded border-gray-300 dark:bg-gray-800">
                        </div>
                    </div>

                    {{-- MCQ fields --}}
                    @if ($qt === 'MCQ')
                        <div class="grid grid-cols-2 gap-4">
                            @foreach (['A','B','C','D'] as $opt)
                                <div>
                                    <label class="block text-sm font-medium mb-1">Answer {{ $opt }}</label>
                                    <input type="text"
                                           name="answer_{{ strtolower($opt) }}"
                                           value="{{ old('answer_'.strtolower($opt)) }}"
                                           class="w-full rounded border-gray-300 dark:bg-gray-800" required>
                                </div>
                            @endforeach
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Correct Choice</label>
                            <select name="correct_choice" class="w-full rounded border-gray-300 dark:bg-gray-800" required>
                                @foreach (['A','B','C','D'] as $c)
                                    <option value="{{ $c }}" @selected(old('correct_choice')===$c)>{{ $c }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif

                    {{-- TRUE/FALSE fields --}}
                    @if ($qt === 'TRUE_FALSE')
                        <div>
                            <label class="block text-sm font-medium mb-1">Correct Answer</label>
                            <div class="flex items-center gap-6">
                                <label class="inline-flex items-center">
                                    <input type="radio" name="correct_bool" value="1" class="mr-2"
                                        {{ old('correct_bool','1')==='1' ? 'checked' : '' }}>
                                    True
                                </label>
                                <label class="inline-flex items-center">
                                    <input type="radio" name="correct_bool" value="0" class="mr-2"
                                        {{ old('correct_bool')==='0' ? 'checked' : '' }}>
                                    False
                                </label>
                            </div>
                        </div>
                    @endif

                    {{-- SHORT ANSWER fields (maps to SUBJECTIVE) --}}
                    @if ($qt === 'SHORT_ANSWER')
                        <div>
                            <label class="block text-sm font-medium mb-1">Correct Answer (exact match)</label>
                            <input type="text" name="correct_text" value="{{ old('correct_text') }}"
                                   class="w-full rounded border-gray-300 dark:bg-gray-800" required>
                        </div>
                    @endif

                    <div class="pt-4">
                        <button class="rounded-lg px-4 py-2 bg-indigo-600 text-white hover:bg-indigo-700">
                            Save Question
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
