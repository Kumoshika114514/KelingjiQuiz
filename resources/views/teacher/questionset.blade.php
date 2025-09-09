<x-teacher>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Question Set
            </h2>

            <div class="flex items-center gap-2">
                <a href="{{ route('teacher.questions.index', [$questionSet->class_id, $questionSet->id]) }}"
                   class="inline-flex items-center rounded-lg px-4 py-2 bg-indigo-600 text-white hover:bg-indigo-700">
                    Manage Questions
                </a>
                <a href="{{ route('teacher.quizclass', $questionSet->class_id) }}"
                   class="inline-flex items-center rounded-lg px-4 py-2 bg-gray-100 dark:bg-gray-800 text-gray-800 dark:text-gray-100 hover:bg-gray-200">
                    Back to Class
                </a>
            </div>
        </div>
    </x-slot>

    @php
        // Safe date handling (no Blade imports)
        $start = $questionSet->start_time ? \Illuminate\Support\Carbon::parse($questionSet->start_time) : null;
        $end   = $questionSet->end_time   ? \Illuminate\Support\Carbon::parse($questionSet->end_time)   : null;
        $now   = \Illuminate\Support\Carbon::now();

        $status = 'Open';
        if ($start && $now->lt($start))      $status = 'Scheduled';
        elseif ($end && $now->gt($end))      $status = 'Expired';

        $startStr = $start ? $start->format('Y-m-d H:i') : '—';
        $endStr   = $end   ? $end->format('Y-m-d H:i')   : '—';
    @endphp

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="mb-4 rounded-md bg-green-50 p-4 text-green-700">
                    {{ session('success') }}
                </div>
            @endif

            {{-- Title --}}
            <div class="mb-4 flex items-center gap-3">
                <p class="text-2xl font-semibold text-gray-900 dark:text-white">
                    {{ $questionSet->topic }}
                </p>

                {{-- Status badge --}}
                <span class="px-2 py-1 text-xs rounded-full
                    @if($status === 'Scheduled') bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300
                    @elseif($status === 'Expired') bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300
                    @else bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300 @endif">
                    {{ $status }}
                </span>
            </div>

            {{-- Quick details --}}
            <div class="bg-white dark:bg-gray-900 shadow sm:rounded-lg">
                <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                    <div class="space-y-1">
                        <div class="text-gray-600 dark:text-gray-300">Question Type</div>
                        <div class="font-medium text-gray-900 dark:text-gray-100">
                            {{ strtoupper($questionSet->question_type) }}
                        </div>
                    </div>

                    <div class="space-y-1">
                        <div class="text-gray-600 dark:text-gray-300">Answer Time (set)</div>
                        <div class="font-medium text-gray-900 dark:text-gray-100">
                            {{ $questionSet->answer_time }} min
                        </div>
                    </div>

                    <div class="space-y-1">
                        <div class="text-gray-600 dark:text-gray-300">Start Time</div>
                        <div class="font-medium text-gray-900 dark:text-gray-100">
                            {{ $startStr }}
                        </div>
                    </div>

                    <div class="space-y-1">
                        <div class="text-gray-600 dark:text-gray-300">End Time</div>
                        <div class="font-medium text-gray-900 dark:text-gray-100">
                            {{ $endStr }}
                        </div>
                    </div>

                    <div class="space-y-1">
                        <div class="text-gray-600 dark:text-gray-300">Active</div>
                        <div class="font-medium">
                            @if($questionSet->is_active)
                                <span class="text-green-700 dark:text-green-400">Yes</span>
                            @else
                                <span class="text-red-700 dark:text-red-400">No</span>
                            @endif
                        </div>
                    </div>

                    <div class="space-y-1">
                        <div class="text-gray-600 dark:text-gray-300">Real-time Session</div>
                        <div class="font-medium">
                            {{ $questionSet->is_realtime ? 'Enabled' : 'Disabled' }}
                        </div>
                    </div>

                    <div class="space-y-1">
                        <div class="text-gray-600 dark:text-gray-300">Question Count</div>
                        <div class="font-medium text-gray-900 dark:text-gray-100">
                            {{ $questionSet->question_count ?? 0 }}
                        </div>
                    </div>

                    <div class="space-y-1 md:col-span-2">
                        <div class="text-gray-600 dark:text-gray-300">Description</div>
                        <div class="font-medium text-gray-900 dark:text-gray-100">
                            {{ $questionSet->description ?? '—' }}
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-teacher>
