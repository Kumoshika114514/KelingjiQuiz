<x-teacher>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ $quizClass->name }}
            </h2>
            <a href="{{ route('quizclasses.edit', $quizClass->id) }}"
                class=" hover:bg-blue-700 text-white py-2 px-4 rounded-lg shadow text-base">
                Edit
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="text-white">
                {{ $quizClass->description }}
            </div>
            <h2 class="text-white text-xl font-bold">Students Joined</h2>
            <hr>
            <ul class="text-white mt-4">
                @forelse ($quizClass->students as $student)
                    <li class="mb-2">{{ $student->name }} ({{ $student->email }})</li>
                @empty
                    <li>No students joined yet.</li>
                @endforelse
            </ul>

            <h2 class="text-white text-xl font-bold">Question Sets</h2>
            <hr>
            <ul class="text-white mt-4">
                @forelse ($quizClass->questionSets as $questionSet)
                    <li class="mb-2">{{ $$questionSet->topic }}</li>
                    <li class="mb-2">{{ $$questionSet->description }}</li>
                @empty
                    <li>Start create a question set!</li>
                @endforelse
            </ul>
        </div>
    </div>
</x-teacher>