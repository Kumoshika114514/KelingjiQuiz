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
                   class="inline-flex items-center rounded-lg px-4 py-2 bg-gray-300 dark:bg-gray-800 text-gray-800 dark:text-gray-100 hover:bg-gray-200">
                    Back to Class
                </a>
            </div>
        </div>
    </x-slot>

    @php
        // Time window
        $start = $questionSet->start_time ? \Illuminate\Support\Carbon::parse($questionSet->start_time) : null;
        $end   = $questionSet->end_time   ? \Illuminate\Support\Carbon::parse($questionSet->end_time)   : null;
        $now   = \Illuminate\Support\Carbon::now();

        $startStr = $start ? $start->format('Y-m-d H:i') : '—';
        $endStr   = $end   ? $end->format('Y-m-d H:i')   : '—';

        // Window label (informational only)
        $windowLabel = '—';
        $windowClass = 'bg-gray-200 text-gray-800';
        if ($start && $end) {
            if ($now->between($start, $end)) {
                $windowLabel = 'Window Open';
                $windowClass = 'bg-blue-100 text-blue-800';
            } elseif ($now->lt($start)) {
                $windowLabel = 'Scheduled';
                $windowClass = 'bg-yellow-100 text-yellow-800';
            } else {
                $windowLabel = 'Expired';
                $windowClass = 'bg-red-100 text-red-800';
            }
        }

        // State badge (authoritative)
        $state = strtoupper($questionSet->state ?? '');
        [$stateLabel, $stateClass] = match ($state) {
            'ACTIVE'    => ['Active',    'bg-green-100 text-green-800'],
            'SCHEDULED' => ['Scheduled', 'bg-yellow-100 text-yellow-800'],
            'CLOSED'    => ['Closed',    'bg-red-100 text-red-700'],
            'ARCHIVED'  => ['Archived',  'bg-slate-200 text-slate-700'],
            'DRAFT'     => ['Draft',     'bg-gray-200 text-gray-800'],
            default     => [
                $questionSet->is_active ? 'Active' : 'Closed',
                $questionSet->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-700'
            ],
        };
    @endphp


    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <p class="dark:text-white">{{ $questionSet->topic }}</p>

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

                {{-- STATE badge (replaces old "Open" label) --}}
                <span class="px-2 py-1 text-xs rounded-full {{ $stateClass }}">
                    {{ $stateLabel }}
                </span>
            </div>

            {{-- Quick details --}}
            <div class="bg-gray-200 dark:bg-gray-800 shadow sm:rounded-lg">
                <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">

                    <div class="space-y-1">
                        <div class="text-gray-600 dark:text-gray-200">Question Type</div>
                        <div class="font-medium text-gray-900 dark:text-gray-200">
                            {{ strtoupper($questionSet->question_type) }}
                        </div>
                    </div>

                    <div class="space-y-1">
                        <div class="text-gray-600 dark:text-gray-200">Answer Time (set)</div>
                        <div class="font-medium text-gray-900 dark:text-gray-200">
                            {{ $questionSet->answer_time }} min
                        </div>
                    </div>

                    <div class="space-y-1">
                        <div class="text-gray-600 dark:text-gray-200">Start Time</div>
                        <div class="font-medium text-gray-900 dark:text-gray-200">
                            {{ $startStr }}
                        </div>
                    </div>

                    <div class="space-y-1">
                        <div class="text-gray-600 dark:text-gray-200">End Time</div>
                        <div class="font-medium text-gray-900 dark:text-gray-200">
                            {{ $endStr }}
                        </div>
                    </div>

                    <div class="space-y-1">
                        <div class="text-gray-600 dark:text-gray-200">Active</div>
                        <div class="font-medium">
                            @if($questionSet->is_active)
                                <span class="text-green-700 dark:text-green-400">Yes</span>
                            @else
                                <span class="text-red-700 dark:text-red-400">No</span>
                            @endif
                        </div>
                    </div>

                    <div class="space-y-1">
                        <div class="text-gray-600 dark:text-gray-200">Real-time Session</div>
                        <div class="font-medium text-gray-500 dark:text-gray-200">
                            {{ $questionSet->is_realtime ? 'Enabled' : 'Disabled' }}
                        </div>
                    </div>

                    <div class="space-y-1">
                        <div class="text-gray-600 dark:text-gray-200">Question Count</div>
                        <div class="font-medium text-gray-900 dark:text-gray-200">
                            {{ $questionSet->question_count ?? 0 }}
                        </div>
                    </div>

                    <div class="space-y-1 md:col-span-2">
                        <div class="text-gray-600 dark:text-gray-200">Description</div>
                        <div class="font-medium text-gray-900 dark:text-gray-200">
                            {{ $questionSet->description ?? '—' }}
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-teacher>
