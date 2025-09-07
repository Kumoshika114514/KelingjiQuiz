<!-- teacher/quizclass.blade.php -->
<x-teacher>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 id="className" class="font-semibold text-xl text-gray-800 leading-tight">Class</h2>
            <div class="flex items-center space-x-3">
                <a href="{{ route('quizclasses.edit', $quizClassId) }}"
                    class="bg-gray-100 hover:bg-gray-200 text-gray-800 px-3 py-2 rounded shadow-sm">
                    Edit
                </a>

                <a href="{{ route('quizclasses.questionsets.create', $quizClassId) }}"
                    class="bg-blue-600 hover:bg-blue-700 dark:text-white px-3 py-2 rounded shadow">
                    New Quiz
                </a>
            </div>

        </div>
    </x-slot>

    <div class="py-8" x-data="quizClassPage({{ json_encode($quizClassId) }})" x-init="init()" x-cloak>
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 mt-6">
            <!-- Class Meta -->
            <div class="mb-6 text-gray-700 dark:text-white">
                <template x-if="loadingMeta">
                    <div class="p-4 bg-white rounded shadow text-gray-500">Loading class...</div>
                </template>

                <template x-if="!loadingMeta">
                    <div>
                        <p class="mb-2" x-text="quizClass.description"></p>

                        <div class="flex items-center gap-2 mb-4">
                            <span id="classCode" class="font-mono px-2 py-1 bg-gray-100 rounded"
                                x-text="shownCode">*****</span>
                            <button type="button" @click="toggleCode()"
                                class="px-2 py-1 bg-blue-500 dark:text-white rounded hover:bg-blue-600">
                                <span x-text="codeVisible ? 'Hide' : 'Show'"></span>
                            </button>
                        </div>
                    </div>
                </template>
            </div>

            <!-- Tabs -->
            <div class="flex items-end gap-2 mb-0">
                <button @click="showQuestions()"
                    :class="active === 'questions' ? 'bg-white text-gray-900 -mb-1 z-20 shadow-md' : 'bg-gray-100 text-gray-600 translate-y-1 z-10'"
                    class="px-4 py-2 rounded-t-lg border border-b-0 transition-all">
                    Question Sets
                </button>

                <button @click="showStudents()"
                    :class="active === 'students' ? 'bg-white text-gray-900 -mb-1 z-20 shadow-md' : 'bg-gray-100 text-gray-600 translate-y-1 z-10'"
                    class="px-4 py-2 rounded-t-lg border border-b-0 transition-all">
                    Students Joined
                </button>
            </div>

            <!-- Content -->
            <div class="bg-white rounded-b-lg border border-t-0 shadow-sm p-6 mt-0">

                <!-- Question Sets (default loaded) -->
                <div x-show="active === 'questions'" x-transition>
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-800">Question Sets</h3>
                        <a href="{{ route('quizclasses.questionsets.create', $quizClassId) }}"
                            class="bg-blue-600 hover:bg-blue-700 dark:text-white px-3 py-2 rounded shadow">
                            New Quiz
                        </a>
                    </div>
                    <hr class="mb-4">

                    <template x-if="loadingQuestionSets">
                        <div class="text-gray-500">Loading question sets...</div>
                    </template>

                    <template x-if="!loadingQuestionSets">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <template x-if="questionSets.length === 0">
                                <div class="text-gray-600">No question set has been created yet. Create a question set
                                    to get started.</div>
                            </template>

                            <template x-for="set in questionSets" :key="set.id">
                                <a :href="`/teacher/quizclass/${quizClassId}/questionsets/${set.id}`"
                                    class="block p-4 rounded-lg border hover:shadow-md transition bg-gray-50">
                                    <div class="font-semibold text-gray-800" x-text="set.topic"></div>
                                    <div class="text-sm text-gray-500 mt-1">
                                        Type: <span x-text="formatType(set.question_type)"></span>
                                    </div>
                                </a>
                            </template>

                        </div>
                    </template>
                </div>

                <!-- Students (lazy loaded) -->
                <div x-show="active === 'students'" x-transition>
                    <h3 class="text-lg font-semibold mb-3 text-gray-800">Students Joined</h3>
                    <hr class="mb-4">

                    <template x-if="loadingStudents">
                        <div class="text-gray-500">Loading students...</div>
                    </template>

                    <template x-if="!loadingStudents">
                        <ul class="space-y-3">
                            <template x-if="students.length === 0">
                                <li class="text-gray-600">No students joined yet.</li>
                            </template>

                            <template x-for="student in students" :key="student.id">
                                <li class="flex items-center justify-between">
                                    <div>
                                        <div class="font-medium text-gray-800" x-text="student.name"></div>
                                        <div class="text-sm text-gray-500" x-text="student.email"></div>
                                    </div>

                                    <form :action="`/studentclasses/${quizClassId}/${student.id}`" method="POST"
                                        onsubmit="return confirm('Are you sure you want to remove this student from the class?')"
                                        class="ml-4">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                            class="text-red-500 hover:underline px-3 py-1 rounded bg-red-50 border border-red-100">
                                            Kick
                                        </button>
                                    </form>
                                </li>
                            </template>

                        </ul>
                    </template>
                </div>

            </div>
        </div>
    </div>

    <script>
        function quizClassPage(quizClassId) {
            return {
                quizClassId,
                quizClass: {},

                active: 'questions',
                codeVisible: false,
                shownCode: '*****',

                questionSets: [],
                students: [],

                loadingMeta: true,
                loadingQuestionSets: true,
                loadingStudents: false,
                studentsLoaded: false,

                init() {
                    this.loadMeta();
                    this.loadQuestionSets();
                },

                async loadMeta() {
                    try {
                        const res = await fetch(`/api/teacher/quizclass/${this.quizClassId}`, {
                            credentials: 'include',
                            headers: { 'Accept': 'application/json' }
                        });
                        if (!res.ok) throw new Error('Failed to load class');
                        const data = await res.json();

                        this.quizClass = data.quizClass ?? data.quiz_class ?? data;
                        document.getElementById("className").innerHTML = data.quizClass.name;

                        this.shownCode = this.codeVisible && this.quizClass.class_code ? this.quizClass.class_code : '*****';
                    } catch (err) {
                        console.error(err);
                        window.dispatchEvent(new CustomEvent('notice', { detail: { type: 'error', text: 'Failed to load class.' } }));
                    } finally {
                        this.loadingMeta = false;
                    }
                },

                // question sets (load by default)
                async loadQuestionSets() {
                    this.loadingQuestionSets = true;
                    try {
                        const res = await fetch(`/api/teacher/quizclass/${this.quizClassId}/questionsets`, {
                            credentials: 'include',
                            headers: { 'Accept': 'application/json' }
                        });
                        if (!res.ok) throw new Error('Failed to load question sets');
                        const data = await res.json();
                        this.questionSets = data.questionSets ?? data.question_sets ?? [];
                    } catch (err) {
                        console.error(err);
                        window.dispatchEvent(new CustomEvent('notice', { detail: { type: 'error', text: 'Failed to load question sets.' } }));
                    } finally {
                        this.loadingQuestionSets = false;
                    }
                },

                // students (only load when users click the Students tab)
                async loadStudents() {
                    if (this.studentsLoaded) return;
                    this.loadingStudents = true;
                    try {
                        const res = await fetch(`/api/teacher/quizclass/${this.quizClassId}/students`, {
                            credentials: 'include',
                            headers: { 'Accept': 'application/json' }
                        });
                        if (!res.ok) throw new Error('Failed to load students');
                        const data = await res.json();
                        this.students = data.students ?? [];
                        this.studentsLoaded = true;
                    } catch (err) {
                        console.error(err);
                        window.dispatchEvent(new CustomEvent('notice', { detail: { type: 'error', text: 'Failed to load students.' } }));
                    } finally {
                        this.loadingStudents = false;
                    }
                },

                // show handlers
                showQuestions() {
                    this.active = 'questions';
                },

                showStudents() {
                    this.active = 'students';
                    if (!this.studentsLoaded) this.loadStudents();
                },

                // toggle join code visible and hidden
                toggleCode() {
                    this.codeVisible = !this.codeVisible;
                    this.shownCode = this.codeVisible && this.quizClass.class_code ? this.quizClass.class_code : '*****';
                },

                formatType(type) {
                    return (type ?? '').replace('_', ' ');
                },
            }
        }
    </script>
</x-teacher>