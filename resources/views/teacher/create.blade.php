<x-teacher>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-800">Create New Class</h2>
    </x-slot>

    <div class="p-4 max-w-xl mx-auto">
        <form method="POST" action="{{ route('quizclasses.store') }}">
            @csrf
            <div>
                <label class="block text-white">Class Name:</label>
                <input type="text" name="name" class="w-full rounded border p-2" required>
            </div>
            <div class="mt-4">
                <label class="block text-white">Description:</label>
                <textarea name="description" class="w-full rounded border p-2"></textarea>
            </div>
            <div class="mt-4">
                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">Create</button>
            </div>
        </form>
    </div>
</x-teacher>
