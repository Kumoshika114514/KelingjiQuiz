<x-app-layout>
    {{-- CSRF meta (used by fetch). If your layout already has this, keeping it here is harmless. --}}
    <meta name="csrf-token" content="{{ csrf_token() }}">

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

                    // derive a display state if available; otherwise fallback to is_active
                    $displayState = $questionSet->state ?? ($questionSet->is_active ? 'ACTIVE' : 'DISABLED');
                    $isActive = strtoupper($displayState) === 'ACTIVE' || ($questionSet->is_active ?? false);
                @endphp

                <div class="text-sm text-gray-600 dark:text-gray-300">
                    Type: <span class="font-medium">{{ strtoupper($questionSet->question_type) }}</span> ·
                    Window: {{ $startStr }} — {{ $endStr }} ·
                    Status:
                    <span
                        id="qs-status-{{ $questionSet->id }}"
                        class="inline-flex items-center rounded px-2 py-0.5 text-xs
                        {{ (strtoupper($questionSet->state ?? ($questionSet->is_active ? 'ACTIVE' : 'DISABLED')) === 'ACTIVE')
                            ? 'bg-green-100 text-green-700'
                            : 'bg-gray-100 text-gray-700' }}">
                        {{ strtoupper($questionSet->state ?? ($questionSet->is_active ? 'ACTIVE' : 'DISABLED')) }}
                    </span>
                </div>

                <div class="space-x-2">
                    {{-- Toggle Status (calls your proxy route) --}}
                    <button
                        type="button"
                        onclick="toggleQuestionSet({{ $quizClass->id }}, {{ $questionSet->id }})"
                        class="inline-flex items-center rounded-lg px-4 py-2 bg-amber-600 text-white hover:bg-amber-700">
                        Toggle Status
                    </button>

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
                                        <td class="py-2 pr-4">{{ $loop->iteration }}</td>
                                        <td class="py-2 pr-4 max-w-xl truncate">{{ $q->text ?? '—' }}</td>
                                        <td class="py-2 pr-4">{{ $q->points ?? 0 }}</td>
                                        <td class="py-2 pr-4">{{ $q->order ?? 0 }}</td>
                                        <td class="py-2 pr-4 space-x-2">
                                            <a class="text-indigo-600 hover:underline"
                                               href="{{ route('teacher.questions.show', [$quizClass->id, $questionSet->id, $q->id]) }}">View</a>
                                            <a class="text-gray-600 dark:text-gray-200 hover:underline"
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

    {{-- Toggle script --}}
    <script>
        async function toggleQuestionSet(quizClassId, questionSetId) {
            const url = "{{ route('integrations.classsvc.questionsets.toggle', ['quizclass' => '__QC__', 'questionset' => '__QS__']) }}"
                .replace('__QC__', String(quizClassId))
                .replace('__QS__', String(questionSetId));

            try {
                const res = await fetch(url, {
                    method: 'PATCH',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    }
                });

                const data = await res.json();

                if (!res.ok) {
                    alert(data.message || 'Toggle failed');
                    return;
                }

                // Elements to update
                const statusEl = document.getElementById('qs-status-' + questionSetId);
                const activeEl = document.getElementById('qs-active-' + questionSetId);

                // Determine new state from payload if provided, else flip current UI
                let newState;
                if (typeof data.status !== 'undefined') {
                    if (data.status === 1 || String(data.status).toUpperCase() === 'ACTIVE') newState = 'ACTIVE';
                    else if (data.status === 0 || String(data.status).toUpperCase() === 'DISABLED') newState = 'DISABLED';
                }
                if (!newState && statusEl) {
                    newState = (statusEl.textContent.trim().toUpperCase() === 'ACTIVE') ? 'DISABLED' : 'ACTIVE';
                }

                // Update status badge
                if (statusEl && newState) {
                    statusEl.textContent = newState;
                    statusEl.className = 'inline-flex items-center rounded px-2 py-0.5 text-xs ' +
                        (newState === 'ACTIVE'
                            ? 'bg-green-100 text-green-700'
                            : 'bg-gray-100 text-gray-700');
                }

                // Update Active: Yes/No text color
                if (activeEl && newState) {
                    const isActive = newState === 'ACTIVE';
                    activeEl.textContent = isActive ? 'Yes' : 'No';
                    activeEl.className = isActive ? 'text-green-600' : 'text-red-600';
                }

                if (data.message) {
                    console.log(data.message);
                }
            } catch (e) {
                alert('Network error while toggling.');
            }
        }
    </script>
</x-app-layout>
