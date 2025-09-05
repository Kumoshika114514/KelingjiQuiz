<x-teacher>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-2xl text-gray-800 leading-tight">
                {{ __('My Classes') }}
            </h2>
            <a href="{{ route('quizclasses.create') }}"
                class="bg-blue-600 hover:bg-blue-700 dark:text-white font-medium py-2 px-4 rounded-lg shadow transition">
                Create Class
            </a>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 mt-6">

            @if($quizClasses->isEmpty())
                <div class="bg-white shadow rounded-lg p-6 text-center">
                    <p class="text-gray-600">You don't have any classes yet.</p>
                </div>
            @else
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach ($quizClasses as $class)
                        <a href="{{ route('teacher.quizclass', $class->id) }}"
                            class="block bg-white rounded-xl shadow hover:shadow-lg transition transform hover:-translate-y-1 p-6">

                            <div class="flex justify-between items-center mb-3">
                                <h3 class="text-lg font-semibold text-gray-900">{{ $class->name }}</h3>
                                <span class="text-xs px-2 py-1 bg-green-100 text-green-700 rounded-full">
                                    Active
                                </span>
                            </div>

                            <p class="text-sm text-gray-600">
                                Created on {{ $class->created_at->format('M d, Y') }}
                            </p>

                            <div class="mt-4 flex justify-between items-center">
                                <span class="text-blue-600 text-sm font-medium hover:underline">View Details →</span>
                            </div>
                        </a>
                    @endforeach
                </div>
            @endif

        </div>
    </div>
</x-teacher>