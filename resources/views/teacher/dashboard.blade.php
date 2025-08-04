<x-teacher>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Dashboard') }}
            </h2>
            <a href="{{ route('quizclasses.create') }}"
                class=" hover:bg-blue-700 text-white py-2 px-4 rounded-lg shadow text-base">
                Create Class
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            {{-- Your content here --}}
            @forelse ($quizClasses as $class)
                <a href="{{ route('teacher.quizclass', $class->id) }}"
                    class="block mb-4 p-4 rounded shadow hover:bg-gray-600 transition">
                    <h3 class="text-lg font-semibold text-white">{{ $class->name }}</h3>
                    <p class="text-sm text-white">Created By: {{ $class->created_at }}</p>
                </a>
            @empty
                <p class="text-white bg-transparent">You don't have any classes yet.</p>
            @endforelse
        </div>
    </div>
</x-teacher>