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
            <a href="{{ route('quizclasses.questionsets.create', $quizClass->id) }}"
                class="hover:bg-blue-700 text-white py-2 px-4 rounded-lg shadow text-base">
                New Quiz
            </a>

        </div>
        <script>
            function confirmKick(e) {
                // first confirm
                if (!confirm("Are you sure you want to remove this student from the class?")) {
                    return false;
                }
                return true; // allow form to submit
            }
        </script>
        <script>
            document.addEventListener("DOMContentLoaded", function () {
                const classCode = @json($quizClass->class_code);
                const codeElement = document.getElementById("classCode");
                const toggleBtn = document.getElementById("toggleBtn");

                let isShown = false;

                toggleBtn.addEventListener("click", function () {
                    if (isShown) {
                        codeElement.textContent = "*****";
                        toggleBtn.textContent = "Show";
                    } else {
                        codeElement.textContent = classCode;
                        toggleBtn.textContent = "Hide";
                    }
                    isShown = !isShown;
                });
            });
        </script>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="text-white">
                {{ $quizClass->description }}
            </div>
            <div class="text-white flex items-center space-x-2">
                <span id="classCode">*****</span>
                <button id="toggleBtn" type="button" class="px-2 py-1 bg-blue-500 text-white rounded hover:bg-blue-600">
                    Show
                </button>
            </div>
            <h2 class="text-white text-xl font-bold">Students Joined</h2>
            <hr>
            <ul class="text-white mt-4">
                @forelse ($quizClass->students as $student)
                    <li class="mb-2">{{ $student->name }} ({{ $student->email }})</li>
                    <form action="{{ route('studentclasses.destroy', [$student->id, $quizClass->id]) }}" method="POST"
                        class="inline-block" onsubmit="return confirmKick(event)">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-red-500 hover:underline">Kick</button>
                    </form>

                @empty
                    <li>No students joined yet.</li>
                @endforelse
            </ul>

            <h2 class="text-white text-xl font-bold">Question Sets</h2>
            <hr>
                @forelse ($quizClass->questionSets as $questionSet)
                    <a href="{{ route('teacher.quizclass.questionset', ['quizClass' => $quizClass->id, 'questionSet' => $questionSet->id]) }}"
                        class="block mb-4 p-4 rounded shadow hover:bg-gray-600 transition">
                        <p class="mb-2 text-white">{{ $questionSet->topic }}</p>
                    </a>                    
                @empty
                    <p>No question set has been created yet. Start create a question set!</p>
                @endforelse
            
        </div>
    </div>
</x-teacher>