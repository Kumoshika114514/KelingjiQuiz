<x-teacher>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-800">Create New Question Set</h2>
    </x-slot>

    <div class="p-4 max-w-xl mx-auto">
        <form method="POST" action="{{ route('quizclasses.questionsets.store', $quizclassId) }}">
            @csrf

            <!-- Topic -->
            <div class="mt-4">
                <label class="block text-white">Topic:</label>
                <input type="text" name="topic" class="w-full rounded border p-2" required>
            </div>

            <!-- Description -->
            <div class="mt-4">
                <label class="block text-white">Description:</label>
                <textarea name="description" class="w-full rounded border p-2"></textarea>
            </div>

            <!-- Question Type -->
            <div class="mt-4">
                <label class="block text-white">Question Type:</label>
                <select name="question_type" class="w-full rounded border p-2" required>
                    <option value="">-- Select Type --</option>
                    <option value="mcq">Multiple Choice</option>
                    <option value="true_false">True/False</option>
                    <option value="short_answer">Short Answer</option>
                </select>
            </div>

            <!-- Answer Time -->
            <div class="mt-4">
                <label class="block text-white">Answer Time (seconds):</label>
                <input type="number" name="answer_time" class="w-full rounded border p-2" required>
            </div>

            <!-- Start Time -->
            <div class="mt-4">
                <label class="block text-white">Start Time:</label>
                <input type="datetime-local" name="start_time" class="w-full rounded border p-2" required>
            </div>

            <!-- End Time -->
            <div class="mt-4">
                <label class="block text-white">End Time:</label>
                <input type="datetime-local" name="end_time" class="w-full rounded border p-2" required>
            </div>

            <!-- Is Realtime -->
            <div class="mt-4 flex items-center gap-2">
                <input type="checkbox" name="is_realtime" value="1" id="is_realtime">
                <label for="is_realtime" class="text-white">Realtime Mode</label>
            </div>

            <!-- Submit -->
            <div class="mt-6">
                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">
                    Create
                </button>
            </div>
        </form>
    </div>
</x-teacher>
