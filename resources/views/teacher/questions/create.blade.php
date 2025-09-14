<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-100 leading-tight">
            Add Question — {{ $questionSet->topic }} ({{ strtoupper($questionSet->question_type) }})
        </h2>
    </x-slot>

    @php
        // MCQ | TRUE_FALSE | SHORT_ANSWER -> backend enum
        $qt = strtoupper($questionSet->question_type);
        $hiddenType = match ($qt) {
            'MCQ' => 'MCQ',
            'TRUE_FALSE' => 'TRUE_FALSE',
            default => 'SUBJECTIVE',
        };
    @endphp

    <div class="py-6">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="mb-4">
                <a href="{{ route('teacher.questions.index', [$quizClass->id, $questionSet->id]) }}"
                   class="text-l text-gray-600 inline-flex items-center hover:text-gray-900 transition">
                    ← Back to Questions
                </a>
            </div>

            <div class="bg-white dark:bg-gray-900 shadow sm:rounded-lg">
                <form class="p-6 space-y-6"
                      action="{{ route('teacher.questions.store', [$quizClass->id, $questionSet->id]) }}"
                      method="POST"
                      enctype="multipart/form-data"> {{-- needed for image upload --}}
                    @csrf

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

                    {{-- Question text --}}
                    <div>
                        <label class="block text-sm font-medium mb-1">Question Text</label>
                        <textarea name="text" rows="4"
                                  class="w-full rounded border-gray-300 dark:bg-white"
                                  required>{{ old('text') }}</textarea>
                    </div>

                    {{-- Optional image --}}
                    <div>
                        <label class="block text-sm font-medium mb-1">Attach Image (optional)</label>
                        <input type="file"
                               name="image"
                               accept="image/*"
                               class="block w-full rounded border-gray-300 file:mr-4 file:py-2 file:px-3 file:rounded file:border-0 file:bg-gray-100 file:text-gray-700 hover:file:bg-gray-200 dark:bg-white">
                        <p class="text-xs text-gray-500 mt-1">Max 3 MB. JPG, PNG, WebP, GIF, etc.</p>
                        @error('image')
                            <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Limits: Points 1–100 (required), Order 1–999 (optional), Time 10–7200 sec (optional) --}}
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label for="points" class="block text-sm font-medium mb-1">Points</label>
                            <input id="points"
                                   type="number"
                                   name="points"
                                   required
                                   min="1" max="100" step="1" inputmode="numeric"
                                   value="{{ old('points', 1) }}"
                                   class="w-full rounded border-gray-300 dark:bg-white"
                                   oninput="this.value = Math.max(1, Math.min(100, this.value||0))">
                            <p class="text-xs text-gray-500 mt-1">Allowed: 1–100</p>
                        </div>

                        <div>
                            <label for="order" class="block text-sm font-medium mb-1">Order (optional)</label>
                            <input id="order"
                                   type="number"
                                   name="order"
                                   min="1" max="999" step="1" inputmode="numeric"
                                   value="{{ old('order') }}"
                                   class="w-full rounded border-gray-300 dark:bg-white"
                                   oninput="if (this.value) this.value = Math.max(1, Math.min(999, this.value))">
                            <p class="text-xs text-gray-500 mt-1">Allowed: 1–999 (leave empty to auto-assign)</p>
                        </div>

                        <div>
                            <label for="time_limit_sec" class="block text-sm font-medium mb-1">Time Limit (sec, optional)</label>
                            <input id="time_limit_sec"
                                   type="number"
                                   name="time_limit_sec"
                                   min="10" max="7200" step="5" inputmode="numeric" placeholder="10–7200"
                                   value="{{ old('time_limit_sec') }}"
                                   class="w-full rounded border-gray-300 dark:bg-white"
                                   oninput="if (this.value) this.value = Math.max(10, Math.min(7200, this.value))">
                            <p class="text-xs text-gray-500 mt-1">Allowed: 10–7200 seconds</p>
                        </div>
                    </div>

                    {{-- MCQ fields --}}
                    @if ($qt === 'MCQ')
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            @foreach (['A','B','C','D'] as $opt)
                                <div>
                                    <label class="block text-sm font-medium mb-1">Answer {{ $opt }}</label>
                                    <input type="text"
                                           name="answer_{{ strtolower($opt) }}"
                                           value="{{ old('answer_'.strtolower($opt)) }}"
                                           class="w-full rounded border-gray-300 dark:bg-white" required>
                                </div>
                            @endforeach
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Correct Choice</label>
                            <select name="correct_choice" class="w-full rounded border-gray-300" required>
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
                            <input type="text"
                                   name="correct_text"
                                   value="{{ old('correct_text') }}"
                                   class="w-full rounded border-gray-300 dark:bg-white"
                                   required>
                        </div>
                    @endif

                    <div class="pt-2">
                        <button class="rounded-lg px-4 py-2 bg-indigo-600 text-white hover:bg-indigo-700">
                            Save Question
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
