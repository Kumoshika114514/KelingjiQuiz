{{-- resources/views/teacher/components/questionset-actions.blade.php --}}
@props(['quizClass', 'qs'])

@php
    $state = strtoupper($qs->state ?? 'DRAFT');
@endphp

{{-- State badge --}}
<span class="inline-flex items-center rounded-full px-2 py-1 text-xs
    @class([
        'bg-gray-200 text-gray-800'   => $state === 'DRAFT',
        'bg-yellow-100 text-yellow-700'=> $state === 'SCHEDULED',
        'bg-green-100 text-green-700' => $state === 'ACTIVE',
        'bg-red-100 text-red-700'     => $state === 'CLOSED',
        'bg-slate-200 text-slate-700' => $state === 'ARCHIVED',
    ])">
  {{ ucfirst(strtolower($state)) }}
</span>

<div class="mt-2 flex items-center gap-2">
    {{-- ACTIVE -> Disable (Close) --}}
    @if ($state === 'ACTIVE')
        <form method="POST" action="{{ route('teacher.questionset.disable', [$quizClass->id, $qs->id]) }}">
            @csrf
            <button type="submit"
                class="rounded border px-3 py-1 text-sm hover:bg-gray-50">
                Disable
            </button>
        </form>
    @endif

    {{-- SCHEDULED -> Activate or Disable (back to Draft) --}}
    @if ($state === 'SCHEDULED')
        <form method="POST" action="{{ route('teacher.questionset.activate', [$quizClass->id, $qs->id]) }}">
            @csrf
            <button type="submit"
                class="rounded bg-indigo-600 px-3 py-1 text-sm text-white hover:bg-indigo-700">
                Activate
            </button>
        </form>

        <form method="POST" action="{{ route('teacher.questionset.disable', [$quizClass->id, $qs->id]) }}">
            @csrf
            <button type="submit"
                class="rounded border px-3 py-1 text-sm hover:bg-gray-50">
                Back to Draft
            </button>
        </form>
    @endif

    {{-- CLOSED -> Activate (re-open) or Archive --}}
    @if ($state === 'CLOSED')
        <form method="POST" action="{{ route('teacher.questionset.activate', [$quizClass->id, $qs->id]) }}">
            @csrf
            <button type="submit"
                class="rounded bg-indigo-600 px-3 py-1 text-sm text-white hover:bg-indigo-700">
                Activate
            </button>
        </form>

        <form method="POST" action="{{ route('teacher.questionset.archive', [$quizClass->id, $qs->id]) }}">
            @csrf
            <button type="submit"
                class="rounded border px-3 py-1 text-sm hover:bg-gray-50">
                Archive
            </button>
        </form>
    @endif

    {{-- DRAFT -> Archive (you can add a "Schedule" button if you collect dates here) --}}
    @if ($state === 'DRAFT')
        <form method="POST" action="{{ route('teacher.questionset.archive', [$quizClass->id, $qs->id]) }}">
            @csrf
            <button type="submit"
                class="rounded border px-3 py-1 text-sm hover:bg-gray-50">
                Archive
            </button>
        </form>
    @endif

    {{-- ARCHIVED -> no actions (read-only). Add buttons if you plan restores. --}}
</div>
