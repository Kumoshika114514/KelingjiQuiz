<x-teacher>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ $quizClass->name }}
            </h2>

            <div class="flex items-center space-x-3">
                <a href="{{ route('quizclasses.edit', $quizClass->id) }}"
                    class="bg-gray-100 hover:bg-gray-200 text-gray-800 px-3 py-2 rounded shadow-sm">Edit</a>

                <a href="{{ route('quizclasses.questionsets.create', $quizClass->id) }}"
                    class="bg-blue-600 hover:bg-blue-700 dark:text-white px-3 py-2 rounded shadow">New Quiz</a>
            </div>
        </div>

        <script>
            // class code toggle script remains fine
            document.addEventListener("DOMContentLoaded", function () {
                const classCode = @json($quizClass->class_code);
                const codeElement = document.getElementById("classCode");
                const toggleBtn = document.getElementById("toggleBtn");

                let isShown = false;

                if (toggleBtn) {
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
                }
            });
        </script>
    </x-slot>

    <div class="py-8" x-data="{ active: 'questions' }">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 mt-6">

            <!-- Description & class code -->
            <div class="mb-6 text-gray-700 dark:text-white">
                <p class="mb-2">{{ $quizClass->description }}</p>

                <div class="flex items-center gap-2 mb-4">
                    <span id="classCode" class="font-mono px-2 py-1 bg-gray-100 rounded">*****</span>
                    <button id="toggleBtn" type="button"
                        class="px-2 py-1 bg-blue-500 dark:text-white rounded hover:bg-blue-600">
                        Show
                    </button>
                </div>
            </div>

            <!-- Tab labels -->
            <div class="flex items-end gap-2 mb-0">
                <button @click="active = 'questions'" :class="active === 'questions'
                    ? 'bg-white text-gray-900 -mb-1 z-20 shadow-md'
                    : 'bg-gray-100 text-gray-600 translate-y-1 z-10'"
                    class="px-4 py-2 rounded-t-lg border border-b-0 transition-all">
                    Question Sets
                </button>

                <button @click="active = 'students'" :class="active === 'students'
                    ? 'bg-white text-gray-900 -mb-1 z-20 shadow-md'
                    : 'bg-gray-100 text-gray-600 translate-y-1 z-10'"
                    class="px-4 py-2 rounded-t-lg border border-b-0 transition-all">
                    Students Joined
                </button>
            </div>

            <!-- Content area -->
            <div class="bg-white rounded-b-lg border border-t-0 shadow-sm p-6 mt-0">

                <!-- Question Sets Section -->
                <div x-show="active === 'questions'" x-transition>
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-800">Question Sets</h3>
                        <a href="{{ route('quizclasses.questionsets.create', $quizClass->id) }}"
                            class="text-sm bg-blue-600 hover:bg-blue-700 dark:text-white px-3 py-1 rounded shadow">
                            New Quiz
                        </a>
                    </div>
                    <hr class="mb-4">

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        @forelse ($quizClass->questionSets as $questionSet)
                            <a href="{{ route('teacher.quizclass.questionset', ['quizClass' => $quizClass->id, 'questionSet' => $questionSet->id]) }}"
                                class="block p-4 rounded-lg border hover:shadow-md transition bg-gray-50">
                                <div class="font-semibold text-gray-800">{{ $questionSet->topic }}</div>
                                <div class="text-sm text-gray-500 mt-1">Type:
                                    {{ ucfirst(str_replace('_', ' ', $questionSet->question_type)) }}
                                </div>
                            </a>
                        @empty
                            <div class="text-gray-600">No question set has been created yet. Create a question set to get
                                started.</div>
                        @endforelse
                    </div>
                </div>

                <!-- Students Section -->
                <div x-show="active === 'students'" x-transition>
                    <h3 class="text-lg font-semibold mb-3 text-gray-800">Students Joined</h3>
                    <hr class="mb-4">

                    <ul class="space-y-3">
                        @forelse ($quizClass->students as $student)
                            <li class="flex items-center justify-between">
                                <div>
                                    <div class="font-medium text-gray-800">{{ $student->name }}</div>
                                    <div class="text-sm text-gray-500">{{ $student->email }}</div>
                                </div>

                                <form action="{{ route('studentclasses.destroy', [$student->id, $quizClass->id]) }}"
                                    method="POST" class="ml-4"
                                    onsubmit="return confirm('Are you sure you want to remove this student from the class?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                        class="text-red-500 hover:underline px-3 py-1 rounded bg-red-50 border border-red-100">
                                        Kick
                                    </button>
                                </form>
                            </li>
                        @empty
                            <li class="text-gray-600">No students joined yet.</li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>
    </div>
</x-teacher>