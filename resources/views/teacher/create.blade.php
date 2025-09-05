<x-teacher>
    <x-slot name="header">
        <h2 class="text-2xl font-semibold text-gray-800 leading-tight">
            Create New Class
        </h2>
    </x-slot>

    <div class="max-w-xl mx-auto mt-8">
        <div class="bg-white shadow rounded-lg p-6 mt-6">
            <!-- Back Button -->
            <a href="{{ route('teacher.dashboard') }}"
                class="inline-flex items-center text-gray-600 hover:text-gray-900 transition">
                &lt; Back
            </a>
            <form method="POST" action="{{ route('quizclasses.store') }}" class="space-y-6">
                @csrf

                <!-- Class Name -->
                <div>
                    <label class="block text-gray-700 font-medium mb-1">Class Name</label>
                    <input type="text" name="name" value="{{ old('name') }}"
                        class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 p-2"
                        required>
                    @error('name')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Description -->
                <div>
                    <label class="block text-gray-700 font-medium mb-1">Description</label>
                    <textarea name="description"
                        class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 p-2"
                        rows="4">{{ old('description') }}</textarea>
                    @error('description')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Submit -->
                <div class="flex justify-end">
                    <button type="submit"
                        class="bg-blue-600 hover:bg-blue-700 dark:text-white font-medium px-5 py-2 rounded-lg shadow transition">
                        Create Class
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-teacher>