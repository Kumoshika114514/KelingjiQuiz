<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-100 leading-tight">
            Preview (Student View) — {{ $questionSet->topic }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="mb-4">
                <a href="{{ route('teacher.questions.index', [$quizClass->id, $questionSet->id]) }}"
                   class="text-sm text-gray-600 dark:text-gray-200 hover:underline">← Back to Questions</a>
            </div>

            <div class="bg-white dark:bg-white shadow sm:rounded-lg p-6 space-y-6">
                <div>                  
                    <h3 class="mt-2 text-lg font-semibold text-gray-900 dark:text-black">{{ $question->text }}</h3>
                </div>

                @if (!empty($question->image_path))
                    <div>
                        <img
                            src="{{ asset('storage/'.$question->image_path) }}"
                            alt="Question image"
                            class="w-full max-h-80 object-contain rounded border border-gray-200"
                        >
                    </div>
                @endif

                <form>
                    @if ($questionSet->question_type === 'mcq')
                        <div class="space-y-3">
                            @foreach (['A','B','C','D'] as $opt)
                                @php $field='answer_'.strtolower($opt); @endphp
                                <label class="flex items-start gap-3 p-3 rounded border border-gray-200 dark:border-gray-700 cursor-pointer">
                                    <input type="radio" name="preview_answer" class="mt-1">
                                    <span><span class="font-semibold mr-2">{{ $opt }}.</span>{{ $question->$field }}</span>
                                </label>
                            @endforeach
                        </div>
                    @elseif ($questionSet->question_type === 'true_false')
                        <div class="space-y-3">
                            <label class="flex items-center gap-3 p-3 rounded border border-gray-200 dark:border-gray-700 cursor-pointer">
                                <input type="radio" name="preview_tf" class="">
                                <span>True</span>
                            </label>
                            <label class="flex items-center gap-3 p-3 rounded border border-gray-200 dark:border-gray-700 cursor-pointer">
                                <input type="radio" name="preview_tf" class="">
                                <span>False</span>
                            </label>
                        </div>
                    @elseif ($questionSet->question_type === 'short_answer')
                        <div>
                            <input type="text" class="w-full rounded border-gray-300 dark:bg-gray-800" placeholder="Type your answer…">
                        </div>
                    @endif

                    <div class="pt-4">
                        <button type="button" class="rounded-lg px-4 py-2 bg-gray-100 dark:bg-gray-800 text-gray-800 dark:text-gray-100" disabled>
                            Submit (disabled in preview)
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
