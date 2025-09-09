<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-100 leading-tight">
            Edit Question — {{ $questionSet->topic }} ({{ strtoupper($questionSet->question_type) }})
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="mb-4">
                <a href="{{ route('teacher.questions.index', [$quizClass->id, $questionSet->id]) }}"
                   class="text-sm text-gray-600 hover:underline">← Back to Questions</a>
            </div>

            <div class="bg-white dark:bg-gray-900 shadow sm:rounded-lg">
                <form class="p-6 space-y-6"
                      action="{{ route('teacher.questions.update', [$quizClass->id, $questionSet->id, $question->id]) }}"
                      method="POST">
                    @csrf @method('PUT')

                    @if ($errors->any())
                        <div class="rounded-md bg-red-50 p-4 text-red-700">
                            <ul class="list-disc pl-5">
                                @foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach
                            </ul>
                        </div>
                    @endif

                    <div>
                        <label class="block text-sm font-medium mb-1">Question Text</label>
                        <textarea name="text" rows="4" class="w-full rounded border-gray-300 dark:bg-gray-800" required>{{ old('text', $question->text) }}</textarea>
                    </div>

                    <div class="grid grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium mb-1">Points</label>
                            <input type="number" name="points" min="0" value="{{ old('points', $question->points) }}"
                                   class="w-full rounded border-gray-300 dark:bg-gray-800" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Order</label>
                            <input type="number" name="order" min="0" value="{{ old('order', $question->order) }}"
                                   class="w-full rounded border-gray-300 dark:bg-gray-800">
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Time Limit (sec)</label>
                            <input type="number" name="time_limit_sec" min="5" value="{{ old('time_limit_sec', $question->time_limit_sec) }}"
                                   class="w-full rounded border-gray-300 dark:bg-gray-800">
                        </div>
                    </div>

                    @if ($questionSet->question_type === 'mcq')
                        <div class="grid grid-cols-2 gap-4">
                            @foreach (['A','B','C','D'] as $opt)
                                @php $field = 'answer_'.strtolower($opt); @endphp
                                <div>
                                    <label class="block text-sm font-medium mb-1">Answer {{ $opt }}</label>
                                    <input type="text" name="{{ $field }}" value="{{ old($field, $question->$field) }}"
                                           class="w-full rounded border-gray-300 dark:bg-gray-800" required>
                                </div>
                            @endforeach
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Correct Choice</label>
                            <select name="correct_choice" class="w-full rounded border-gray-300 dark:bg-gray-800" required>
                                @foreach (['A','B','C','D'] as $c)
                                    <option value="{{ $c }}" @selected(old('correct_choice', $question->correct_choice)===$c)>{{ $c }}</option>
                                @endforeach
                            </select>
                        </div>
                    @elseif ($questionSet->question_type === 'true_false')
                        <div>
                            <label class="block text-sm font-medium mb-1">Correct Answer</label>
                            <div class="flex items-center gap-6">
                                <label class="inline-flex items-center">
                                    <input type="radio" name="correct_bool" value="1" class="mr-2" {{ old('correct_bool', $question->correct_bool ? '1' : '0')==='1' ? 'checked' : '' }}>
                                    True
                                </label>
                                <label class="inline-flex items-center">
                                    <input type="radio" name="correct_bool" value="0" class="mr-2" {{ old('correct_bool', $question->correct_bool ? '1' : '0')==='0' ? 'checked' : '' }}>
                                    False
                                </label>
                            </div>
                        </div>
                    @elseif ($questionSet->question_type === 'short_answer')
                        <div>
                            <label class="block text-sm font-medium mb-1">Correct Answer (exact match)</label>
                            <input type="text" name="correct_text" value="{{ old('correct_text', $question->correct_text) }}"
                                   class="w-full rounded border-gray-300 dark:bg-gray-800" required>
                        </div>
                    @endif

                    <div class="pt-4">
                        <button class="rounded-lg px-4 py-2 bg-indigo-600 text-white hover:bg-indigo-700">
                            Update Question
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
