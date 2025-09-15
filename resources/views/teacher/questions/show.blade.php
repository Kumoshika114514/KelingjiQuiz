<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-100 leading-tight">
            Question #{{ $question->id }} — {{ $questionSet->topic }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="mb-4 flex items-center justify-between">
                <a href="{{ route('teacher.questions.index', [$quizClass->id, $questionSet->id]) }}"
                   class="text-sm text-gray-600 dark:text-white hover:underline">← Back to Questions</a>
                <div class="space-x-2">
                    <a href="{{ route('teacher.questions.edit', [$quizClass->id, $questionSet->id, $question->id]) }}"
                       class="rounded px-3 py-1 bg-blue-600 text-white hover:bg-blue-700">Edit</a>
                    <a href="{{ route('teacher.questions.preview', [$quizClass->id, $questionSet->id, $question->id]) }}"
                       class="rounded px-3 py-1 bg-gray-100 dark:bg-gray-800 text-gray-800 dark:text-gray-100 hover:bg-gray-200">
                        Preview
                    </a>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-900 shadow sm:rounded-lg p-6 space-y-6">
                {{-- Meta line --}}
                <div class="text-sm text-gray-600 dark:text-gray-300 flex flex-wrap items-center gap-2">
                    <span>Type: <span class="font-medium">{{ strtoupper($questionSet->question_type) }}</span></span>
                    <span class="text-gray-400">·</span>
                    <span>Points: <span class="font-medium">{{ $question->points }}</span></span>
                    <span class="text-gray-400">·</span>
                    <span>Order: <span class="font-medium">{{ $question->order }}</span></span>
                    @if(!empty($question->time_limit_sec))
                        <span class="text-gray-400">·</span>
                        <span>Time Limit:
                            <span class="inline-flex items-center px-2 py-0.5 text-xs rounded-full bg-gray-100 dark:bg-gray-800">
                                {{ $question->time_limit_sec }}s
                            </span>
                        </span>
                    @endif
                </div>

                {{-- Question text --}}
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                    {{ $question->text }}
                </h3>

                {{-- Optional image --}}
                @if (!empty($question->image_path))
                    <div>
                        <img
                            src="{{ asset('storage/'.$question->image_path) }}"
                            alt="Question image"
                            class="max-h-72 rounded border border-gray-200 dark:border-gray-700"
                        >
                    </div>
                @endif

                {{-- Answers --}}
                @if ($questionSet->question_type === 'mcq')
                    <ul class="space-y-2">
                        @foreach (['A','B','C','D'] as $opt)
                            @php $field='answer_'.strtolower($opt); @endphp
                            <li class="p-3 rounded border
                                       @if($question->correct_choice === $opt)
                                           border-green-400 bg-green-50 dark:bg-green-900/20
                                       @else
                                           border-gray-200 dark:border-gray-700
                                       @endif">
                                <span class="font-semibold mr-2">{{ $opt }}.</span>
                                {{ $question->$field }}
                                @if($question->correct_choice === $opt)
                                    <span class="ml-2 text-green-700 dark:text-green-400 text-sm">(correct)</span>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                @elseif ($questionSet->question_type === 'true_false')
                    <p class="text-gray-800 dark:text-gray-100">
                        Correct answer:
                        <span class="font-semibold">{{ $question->correct_bool ? 'True' : 'False' }}</span>
                    </p>
                @elseif ($questionSet->question_type === 'short_answer')
                    <p class="text-gray-800 dark:text-gray-100">
                        Correct answer (exact): <span class="font-semibold">{{ $question->correct_text }}</span>
                    </p>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
