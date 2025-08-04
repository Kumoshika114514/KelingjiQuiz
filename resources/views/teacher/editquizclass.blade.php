<x-teacher>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-800">Edit Class</h2>
    </x-slot>

    <div class="p-4 max-w-xl mx-auto">
        <form method="POST" action="{{ route('quizclasses.update', $quizClass->id) }}">
            @csrf
            @method('PUT')
            <div>
                <label class="block text-white">Class Name:</label>
                <input type="text" name="name" class="w-full rounded border p-2" value="{{ $quizClass->name }}"
                    required>
            </div>
            <div class="mt-4">
                <label class="block text-white">Description:</label>
                <textarea name="description" class="w-full rounded border p-2"
                    required>{{ $quizClass->description }}</textarea>
            </div>
            <div class="mt-4">
                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">Edit</button>
            </div>
        </form>
    </div>
</x-teacher>