<x-app-layout> 
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-100 leading-tight">
            Questions — {{ $questionSet->topic }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="mb-4 rounded-md bg-green-50 p-4 text-green-700">{{ session('success') }}</div>
            @endif

            <div class="mb-4 flex items-center justify-between">
                @php
                    $startStr = $questionSet->start_time
                        ? \Illuminate\Support\Carbon::parse($questionSet->start_time)->format('Y-m-d H:i')
                        : 'Anytime';
                    $endStr = $questionSet->end_time
                        ? \Illuminate\Support\Carbon::parse($questionSet->end_time)->format('Y-m-d H:i')
                        : 'Anytime';
                @endphp

                <div class="text-sm text-gray-600 dark:text-gray-300">
                    Type: <span class="font-medium">{{ strtoupper($questionSet->question_type) }}</span> ·
                    Window: {{ $startStr }} — {{ $endStr }} ·
                    Active:
                    {!! $questionSet->is_active
                        ? '<span class="text-green-600">Yes</span>'
                        : '<span class="text-red-600">No</span>' !!}
                </div>

                <div class="space-x-2">
                    <a href="{{ route('teacher.questions.create', [$quizClass->id, $questionSet->id]) }}"
                       class="inline-flex items-center rounded-lg px-4 py-2 bg-indigo-600 text-white hover:bg-indigo-700">
                        + Add Question
                    </a>
                    <a href="{{ route('teacher.questionset', [$quizClass->id, $questionSet->id]) }}"
                       class="inline-flex items-center rounded-lg px-4 py-2 bg-gray-100 dark:bg-gray-800 text-gray-800 dark:text-gray-100 hover:bg-gray-200">
                        ← Back to Question Set
                    </a>
                </div>
            </div>

            <div class="bg-gray-200 dark:bg-gray-800 shadow sm:rounded-lg">
                <div class="p-6">
                    @if ($questions->isEmpty())
                        <p class="text-gray-500 dark:text-white">No questions yet.</p>
                    @else
                        <table class="min-w-full text-sm">
                            <thead class="text-left text-gray-600 dark:text-white">
                                <tr>
                                    <th class="py-2 pr-4">#</th>
                                    <th class="py-2 pr-4">Question</th>
                                    <th class="py-2 pr-4">Points</th>
                                    <th class="py-2 pr-4">Order</th>
                                    <th class="py-2 pr-4">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="text-gray-800 dark:text-gray-100">
                                @foreach ($questions as $q)
                                    <tr class="border-t border-gray-400 dark:border-gray-700">
                                        <td class="py-2 pr-4">{{ $q->id }}</td>
                                        <td class="py-2 pr-4 max-w-xl truncate">{{ $q->text ?? '—' }}</td>
                                        <td class="py-2 pr-4">{{ $q->points ?? 0 }}</td>
                                        <td class="py-2 pr-4">{{ $q->order ?? 0 }}</td>
                                        <td class="py-2 pr-4 space-x-2">
                                            <a class="text-indigo-600 hover:underline"
                                               href="{{ route('teacher.questions.show', [$quizClass->id, $questionSet->id, $q->id]) }}">View</a>
                                            <a class="text-blue-600 hover:underline"
                                               href="{{ route('teacher.questions.edit', [$quizClass->id, $questionSet->id, $q->id]) }}">Edit</a>
                                            <a class="text-gray-600 dark:text-gray-200 hover:underline"
                                               href="{{ route('teacher.questions.preview', [$quizClass->id, $questionSet->id, $q->id]) }}">Preview</a>
                                            <form class="inline"
                                                  action="{{ route('teacher.questions.destroy', [$quizClass->id, $questionSet->id, $q->id]) }}"
                                                  method="POST"
                                                  onsubmit="return confirm('Delete this question?')">
                                                @csrf
                                                @method('DELETE')
                                                <button class="text-red-600 hover:underline">Delete</button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>
            </div>
        </div>
    </div>  
</x-app-layout> 
