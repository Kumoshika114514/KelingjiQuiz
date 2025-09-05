<x-teacher>
    <x-slot name="header">
        <div class="flex items-center space-x-4">
            <h2 class="text-2xl font-semibold text-gray-800 leading-tight">
                Create New Question Set
            </h2>
        </div>
    </x-slot>

    <div class="max-w-xl mx-auto mt-8">
        <div class="bg-white shadow rounded-lg p-6 mt-6">
            <!-- Back Button -->
            <a href="{{ route('teacher.quizclass', $quizclassId) }}"
                class="inline-flex items-center text-gray-600 hover:text-gray-900 transition">
                &lt; Back
            </a>
            <form method="POST" action="{{ route('quizclasses.questionsets.store', $quizclassId) }}" class="space-y-6">
                @csrf

                <!-- Topic -->
                <div>
                    <label class="block text-gray-700 font-medium mb-1">Topic</label>
                    <input type="text" name="topic" value="{{ old('topic') }}"
                        class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 p-2"
                        required>
                    @error('topic')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Description -->
                <div>
                    <label class="block text-gray-700 font-medium mb-1">Description</label>
                    <textarea name="description" rows="3"
                        class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 p-2">{{ old('description') }}</textarea>
                    @error('description')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Question Type -->
                <div>
                    <label class="block text-gray-700 font-medium mb-1">Question Type</label>
                    <select name="question_type"
                        class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 p-2"
                        required>
                        <option value="">-- Select Type --</option>
                        <option value="mcq" {{ old('question_type') == 'mcq' ? 'selected' : '' }}>Multiple Choice</option>
                        <option value="true_false" {{ old('question_type') == 'true_false' ? 'selected' : '' }}>True/False
                        </option>
                        <option value="short_answer" {{ old('question_type') == 'short_answer' ? 'selected' : '' }}>Short
                            Answer</option>
                    </select>
                    @error('question_type')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Answer Time -->
                <div>
                    <label class="block text-gray-700 font-medium mb-1">Answer Time (seconds)</label>
                    <input type="number" name="answer_time" value="{{ old('answer_time') }}"
                        class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 p-2"
                        required>
                    @error('answer_time')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Start Time -->
                <div>
                    <label class="block text-gray-700 font-medium mb-1">Start Time</label>
                    <input type="datetime-local" name="start_time" value="{{ old('start_time') }}"
                        class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 p-2"
                        required>
                    @error('start_time')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- End Time -->
                <div>
                    <label class="block text-gray-700 font-medium mb-1">End Time</label>
                    <input type="datetime-local" name="end_time" value="{{ old('end_time') }}"
                        class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 p-2"
                        required>
                    @error('end_time')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Is Realtime -->
                <div class="flex items-center">
                    <input type="checkbox" name="is_realtime" value="1" id="is_realtime" 
                    {{ old('is_realtime') ? 'checked' : '' }} 
                    class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500" />
                    <label for="is_realtime" class="ml-2 text-gray-700">Realtime Mode</label>
                </div>

                <!-- Submit -->
                <div class="flex justify-end">
                    <button type="submit"
                        class="bg-blue-600 hover:bg-blue-700 dark:text-white font-medium px-5 py-2 rounded-lg shadow transition">
                        Create Question Set
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-teacher>