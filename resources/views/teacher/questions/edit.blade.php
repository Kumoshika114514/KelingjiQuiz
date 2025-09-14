<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-100 leading-tight">
            Edit Question — {{ $questionSet->topic }} ({{ strtoupper($questionSet->question_type) }})
        </h2>
    </x-slot>

    @php
        // Normalize type once for clean conditionals
        $qt = strtoupper($questionSet->question_type); // MCQ | TRUE_FALSE | SHORT_ANSWER
    @endphp

    <div class="py-6">
        <div class="py-6">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="mb-4">
                <a href="{{ route('teacher.questions.index', [$quizClass->id, $questionSet->id]) }}"
                   class="text-l text-gray-600 hover:underline">← Back to Questions</a>
            </div>

            <div class="bg-white dark:bg-gray-900 shadow sm:rounded-lg">
                <form class="p-6 space-y-6"
                      action="{{ route('teacher.questions.update', [$quizClass->id, $questionSet->id, $question->id]) }}"
                      method="POST"
                      enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    @if ($errors->any())
                        <div class="rounded-md bg-red-50 p-4 text-red-700">
                            <ul class="list-disc pl-5">
                                @foreach ($errors->all() as $e)
                                    <li>{{ $e }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    {{-- Question text --}}
                    <div>
                        <label class="block text-sm font-medium mb-1">Question Text</label>
                        <textarea name="text" rows="4"
                                  class="w-full rounded border-gray-300 dark:bg-white"
                                  required>{{ old('text', $question->text) }}</textarea>
                    </div>

                    {{-- Optional image (show current + allow replace) --}}
                    <div class="space-y-2">
                        <label class="block text-sm font-medium">Attach / Replace Image (optional)</label>

                        @if ($question->image_path)
                            <div class="flex items-center gap-4">
                                <img src="{{ asset('storage/'.$question->image_path) }}"
                                     alt="Current question image"
                                     class="max-h-32 rounded border">
                                <span class="text-xs text-gray-500">Uploading a new image will replace this one.</span>
                            </div>
                        @else
                            <p class="text-xs text-gray-500">You can attach an image to this question (max 3 MB).</p>
                        @endif>

                        <input type="file"
                               name="image"
                               accept="image/*"
                               class="block w-full rounded border-gray-300 file:mr-4 file:py-2 file:px-3 file:rounded file:border-0 file:bg-gray-100 file:text-gray-700 hover:file:bg-gray-200 dark:bg-white">
                        @error('image')
                            <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Limits: Points 1–100 (required), Order 1–999 (optional), Time 10–7200 sec (optional) --}}
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium mb-1">Points</label>
                            <input type="number"
                                   name="points"
                                   required
                                   min="1" max="100" step="1" inputmode="numeric"
                                   value="{{ old('points', $question->points) }}"
                                   class="w-full rounded border-gray-300 dark:bg-white"
                                   oninput="this.value = Math.max(1, Math.min(100, this.value||0))">
                            <p class="text-xs text-gray-500 mt-1">Allowed: 1–100</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-1">Order (optional)</label>
                            <input type="number"
                                   name="order"
                                   min="1" max="999" step="1" inputmode="numeric"
                                   value="{{ old('order', $question->order) }}"
                                   class="w-full rounded border-gray-300 dark:bg-white"
                                   oninput="if (this.value) this.value = Math.max(1, Math.min(999, this.value))">
                            <p class="text-xs text-gray-500 mt-1">Allowed: 1–999</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-1">Time Limit (sec, optional)</label>
                            <input type="number"
                                   name="time_limit_sec"
                                   min="10" max="7200" step="5" inputmode="numeric"
                                   value="{{ old('time_limit_sec', $question->time_limit_sec) }}"
                                   class="w-full rounded border-gray-300 dark:bg-white"
                                   oninput="if (this.value) this.value = Math.max(10, Math.min(7200, this.value))">
                            <p class="text-xs text-gray-500 mt-1">Allowed: 10–7200 seconds</p>
                        </div>
                    </div>

                    {{-- Type-specific fields --}}
                    @if ($qt === 'MCQ')
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            @foreach (['A','B','C','D'] as $opt)
                                @php $field = 'answer_'.strtolower($opt); @endphp
                                <div>
                                    <label class="block text-sm font-medium mb-1">Answer {{ $opt }}</label>
                                    <input type="text"
                                           name="{{ $field }}"
                                           value="{{ old($field, $question->$field) }}"
                                           class="w-full rounded border-gray-300 dark:bg-white"
                                           required>
                                </div>
                            @endforeach
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Correct Choice</label>
                            <select name="correct_choice"
                                    class="w-full rounded border-gray-300 dark:bg-white"
                                    required>
                                @foreach (['A','B','C','D'] as $c)
                                    <option value="{{ $c }}" @selected(old('correct_choice', $question->correct_choice)===$c)>{{ $c }}</option>
                                @endforeach
                            </select>
                        </div>
                    @elseif ($qt === 'TRUE_FALSE')
                        <div>
                            <label class="block text-sm font-medium mb-1">Correct Answer</label>
                            <div class="flex items-center gap-6">
                                <label class="inline-flex items-center">
                                    <input type="radio" name="correct_bool" value="1" class="mr-2"
                                           {{ old('correct_bool', $question->correct_bool ? '1' : '0')==='1' ? 'checked' : '' }}>
                                    True
                                </label>
                                <label class="inline-flex items-center">
                                    <input type="radio" name="correct_bool" value="0" class="mr-2"
                                           {{ old('correct_bool', $question->correct_bool ? '1' : '0')==='0' ? 'checked' : '' }}>
                                    False
                                </label>
                            </div>
                        </div>
                    @elseif ($qt === 'SHORT_ANSWER')
                        <div>
                            <label class="block text-sm font-medium mb-1">Correct Answer (exact match)</label>
                            <input type="text"
                                   name="correct_text"
                                   value="{{ old('correct_text', $question->correct_text) }}"
                                   class="w-full rounded border-gray-300 dark:bg-white"
                                   required>
                        </div>
                    @endif

                    <div class="pt-2">
                        <button class="rounded-lg px-4 py-2 bg-indigo-600 text-white hover:bg-indigo-700">
                            Update Question
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
