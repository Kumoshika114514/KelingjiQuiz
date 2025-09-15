<!-- teacher/quizclass.blade.php -->
<x-teacher>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 id="className" class="font-semibold text-xl text-gray-800 dark:text-white leading-tight">Class</h2>
            <div class="flex items-center space-x-3">
                <a href="{{ route('quizclasses.edit', $quizClassId) }}"
                    class="bg-gray-100 hover:bg-gray-200 text-gray-800 px-3 py-2 rounded shadow-sm">
                    Edit
                </a>

                <a href="{{ route('teacher.dashboard') }}"
                    class="inline-flex items-center rounded-lg px-4 py-2 bg-gray-100 text-gray-800 hover:bg-gray-200">
                    ← Back
                </a>
            </div>

        </div>
    </x-slot>

    <div class="py-8" x-data="quizClassPage({{ json_encode($quizClassId) }})" x-init="init()" x-cloak>
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 mt-6">
            <!-- quiz class description part -->
            <div class="mb-6 text-gray-700 dark:text-white">
                <template x-if="loadingMeta">
                    <div class="p-4 bg-white rounded shadow text-gray-500">Loading class...</div>
                </template>

                <template x-if="!loadingMeta">
                    <div>
                        <p class="mb-2" x-text="quizClass.description"></p>

                        <!-- class code, can toggle visibility -->
                        <div class="flex items-center gap-2 mb-4">
                            <span id="classCode" class="font-mono px-2 py-1 bg-gray-100 dark:bg-black rounded"
                                x-text="shownCode">*****</span>
                            <button type="button" @click="toggleCode()"
                                class="px-2 py-1 bg-blue-500 dark:text-white rounded hover:bg-blue-600">
                                <span x-text="codeVisible ? 'Hide' : 'Show'"></span>
                            </button>
                        </div>
                    </div>
                </template>
            </div>

            <!-- tabs -->
            <div class="flex items-end gap-2 mb-0 dark:bg-gray-800">
                <button @click="showQuestions()"
                    :class="active === 'questions' ? 'bg-white text-gray-900 -mb-1 z-20 shadow-md dark:bg-gray-800' : 'bg-gray-100 text-gray-600 translate-y-1 z-10 dark:bg-gray-800'"
                    class="px-4 py-2 rounded-t-lg border border-b-0 transition-all dark:bg-gray-800 dark:text-gray-200 dark:border-gray-600">
                    Question Sets
                    <span
                        class="ml-2 inline-flex items-center px-2 py-0.5 text-xs rounded-full bg-gray-100 text-gray-700"
                        x-text="totalQuestionSets"></span>
                </button>

                <button @click="showStudents()"
                    :class="active === 'students' ? 'bg-white text-gray-900 -mb-1 z-20 shadow-md' : 'bg-gray-100 text-gray-600 translate-y-1 z-10'"
                    class="px-4 py-2 rounded-t-lg border border-b-0  transition-all dark:bg-gray-800 dark:text-gray-200 dark:border-gray-600">
                    Students Joined
                    <span
                        class="ml-2 inline-flex items-center px-2 py-0.5 text-xs rounded-full bg-gray-100 text-gray-700"
                        x-text="totalStudents"></span>
                </button>
            </div>


            <!-- main content -->
            <div
                class="bg-white rounded-b-lg border border-t-0 shadow-sm p-6 mt-0 dark:bg-gray-800 dark:border-gray-700">

                <!-- question sets part (load by default) -->
                <div x-show="active === 'questions'" x-transition>
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-300">Question Sets</h3>

                        <div class="flex items-center">
                            <!-- filter -->
                            <x-dropdown align="right" width="48">
                                <x-slot name="trigger">
                                    <button
                                        class="p-2 rounded bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 shadow focus:outline-none">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                            stroke="currentColor" class="w-5 h-5 text-gray-600 dark:text-gray-300">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707l-6.414 6.414A1 1 0 0114 13v7l-4-2v-5.586a1 1 0 01-.293-.707L3.293 6.707A1 1 0 013 6V4z" />
                                        </svg>
                                    </button>
                                </x-slot>

                                <!-- status -->
                                <x-slot name="content">
                                    <div class="px-4 py-2 flex">
                                        <label class="space-x-2">
                                            <input type="checkbox" x-model="filters.showActive" checked>
                                            <span>Active &nbsp;</span>
                                        </label>
                                        <label class="space-x-2">
                                            <input type="checkbox" x-model="filters.showDisabled" checked>
                                            <span>Disabled</span>
                                        </label>
                                    </div>
                                    <hr>

                                    <!--topic (name) -->
                                    <div class="px-4 py-2">
                                        <p class="text-sm font-semibold text-gray-600">Sort by Topic</p>
                                        <label class="space-x-2">
                                            <input type="radio" name="sortTopic" value="asc"
                                                x-model="filters.sortTopic">
                                            <span>A → Z &nbsp;</span>
                                        </label>
                                        <label class="space-x-2">
                                            <input type="radio" name="sortTopic" value="desc"
                                                x-model="filters.sortTopic">
                                            <span>Z → A</span>
                                        </label>
                                    </div>
                                    <hr>

                                    <!-- time (start time & end time) -->
                                    <div class="px-4 py-2">
                                        <p class="text-sm font-semibold text-gray-600">Sort by Time</p>
                                        <label class="flex items-center space-x-2">
                                            <input type="radio" name="sortTime" value="start_asc"
                                                x-model="filters.sortTime">
                                            <span>Start Time ↑</span>
                                        </label>
                                        <label class="flex items-center space-x-2">
                                            <input type="radio" name="sortTime" value="start_desc"
                                                x-model="filters.sortTime">
                                            <span>Start Time ↓</span>
                                        </label>
                                        <label class="flex items-center space-x-2">
                                            <input type="radio" name="sortTime" value="end_asc"
                                                x-model="filters.sortTime">
                                            <span>End Time ↑</span>
                                        </label>
                                        <label class="flex items-center space-x-2">
                                            <input type="radio" name="sortTime" value="end_desc"
                                                x-model="filters.sortTime">
                                            <span>End Time ↓</span>
                                        </label>
                                    </div>
                                </x-slot>
                            </x-dropdown>

                            <a href="{{ route('quizclasses.questionsets.create', $quizClassId) }}"
                                class="bg-blue-600 hover:bg-blue-700 dark:text-white px-3 py-2 rounded shadow">
                                New Quiz
                            </a>
                        </div>
                    </div>

                    <hr class="mb-4">

                    <template x-if="loadingQuestionSets">
                        <div class="text-gray-500">Loading question sets...</div>
                    </template>

                    <template x-if="!loadingQuestionSets">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <!-- if no question set yet -->
                            <template x-if="questionSets.length === 0">
                                <div class="text-gray-600">No question set has been created yet. Create a question set
                                    to get started.</div>
                            </template>

                            <template x-for="set in filteredQuestionSets" :key="set.id">
                                <div class="flex items-start space-x-3">
                                    <a :href="`/teacher/quizclass/${quizClassId}/questionsets/${set.id}`"
                                        class="flex-1 block p-4 rounded-lg border transition hover:shadow-md" :class="set.status === 1
                                            ? 'bg-white border-green-200'
                                            : 'bg-gray-50 border-gray-200 opacity-90'">
                                        <div class="flex items-center justify-between">
                                            <div class="min-w-0">
                                                <div class="font-semibold truncate"
                                                    :class="set.status === 1 ? 'text-green-800' : 'text-red-800'"
                                                    x-text="set.topic"></div>

                                                <div class="text-sm text-gray-500 mt-1">
                                                    Type: <span x-text="formatType(set.question_type)"></span>
                                                </div>
                                                <div class="text-sm text-gray-500 mt-1">
                                                    Is Realtime: <span x-text="set.is_realtime == 0 ? 'N' : 'Y'"></span>
                                                </div>
                                                <div class="text-sm text-gray-500 mt-1">
                                                    Highest Score: <span
                                                        x-text="set.highest_score !== null ? formatType(set.highest_score) : '-'"></span>
                                                </div>
                                            </div>

                                            <div class="ml-4 shrink-0">
                                                <!-- status label -->
                                                <span class="px-2 py-0.5 text-xs rounded-full font-medium"
                                                    :class="set.status === 1 ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'"
                                                    x-text="set.status === 1 ? 'Active' : 'Closed'"></span>

                                                <!-- button toggle status -->
                                                <button type="button"
                                                    class="inline-flex items-center px-3 py-2 rounded-md border shadow-sm text-sm font-medium focus:outline-none focus:ring-2 focus:ring-offset-1"
                                                    :class="set.status === 1 ? 'bg-white border-gray-300 hover:bg-gray-50' : 'bg-white border-gray-300 hover:bg-gray-50'"
                                                    :aria-pressed="String(set.status === 1)"
                                                    @click.stop.prevent="toggleStatus(set)"
                                                    :disabled="set._loading === true">
                                                    <template x-if="set._loading">
                                                        <svg class="animate-spin -ml-1 mr-2 h-4 w-4"
                                                            xmlns="http://www.w3.org/2000/svg" fill="none"
                                                            viewBox="0 0 24 24">
                                                            <circle class="opacity-25" cx="12" cy="12" r="10"
                                                                stroke="currentColor" stroke-width="4"></circle>
                                                            <path class="opacity-75" fill="currentColor"
                                                                d="M4 12a8 8 0 018-8v8z"></path>
                                                        </svg>
                                                    </template>

                                                    <template x-if="set.status === 1">
                                                        <span x-text="'Disable'"></span>
                                                    </template>
                                                    <template x-if="set.status === 0">
                                                        <span x-text="'Activate'"></span>
                                                    </template>
                                                </button>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                            </template>

                        </div>
                    </template>
                </div>

                <!-- students part (lazy loaded) -->
                <div x-show="active === 'students'" x-transition>
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold mb-3 text-gray-800 dark:text-gray-200">Students Joined</h3>

                        <!-- search input part -->
                        <div class="relative mb-4">
                            <input type="text" x-model="studentSearch" @input="updateSearch"
                                placeholder="Search Student..." class="w-full px-4 py-2 pr-10 border rounded-lg shadow-sm 
                                focus:ring focus:ring-blue-300 focus:outline-none
                                dark:bg-gray-700 dark:text-gray-200 dark:border-gray-600" />
                        </div>
                    </div>

                    <hr class="mb-4">

                    <template x-if="loadingStudents">
                        <div class="text-gray-500">Loading students...</div>
                    </template>

                    <template x-if="!loadingStudents">
                        <ul class="space-y-3">
                            <!-- if no student joined yet -->
                            <template x-if="students.length === 0">
                                <li class="text-gray-600">No students joined yet.</li>
                            </template>

                            <!-- use filtered students (default is all) -->
                            <template x-for="s in filteredStudents" :key="s.id">
                                <li class="flex items-center justify-between">
                                    <div>
                                        <div class="font-medium text-gray-800 dark:text-gray-300" x-text="s.name">
                                        </div>
                                        <div class="text-sm text-gray-500 dark:text-gray-400" x-text="s.email">
                                        </div>
                                    </div>

                                    <!-- kick student -->
                                    <form :action="`/studentclasses/${quizClassId}/${s.id}`" method="POST"
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

                active: 'questions', // default show question sets
                codeVisible: false, // default join code not visible
                shownCode: '*****', // when not visible show ******

                totalQuestionSets: 0,
                totalStudents: 0,

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
                        if (data.quizClass && data.quizClass.name) {
                            document.getElementById("className").innerHTML = data.quizClass.name;
                        }

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
                        this.totalQuestionSets = Number(data.totalQuestionSets ?? data.total_question_sets ?? data.total ?? this.questionSets.length);

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
                        this.totalStudents = Number(data.totalStudents ?? data.total_students ?? data.total ?? this.students.length);
                        this.studentsLoaded = true;
                    } catch (err) {
                        console.error(err);
                        window.dispatchEvent(new CustomEvent('notice', { detail: { type: 'error', text: 'Failed to load students.' } }));
                    } finally {
                        this.loadingStudents = false;
                    }
                },

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

                // toggle question set status (Active <--> Disabled)
                async toggleStatus(set) {
                    if (!set || set._loading) return;

                    set._loading = true;
                    try {

                        const res = await fetch(`/api/teacher/quizclass/${this.quizClassId}/questionsets/${set.id}/toggle`, {
                            method: 'PATCH',
                            credentials: 'include',
                            headers: {
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            }
                        });

                        if (!res.ok) throw new Error(`Failed to toggle (${res.status})`);

                        const data = await res.json();
                        if (typeof data.status !== 'undefined') {
                            set.status = data.status;
                        } else if (typeof data === 'number') {
                            set.status = data;
                        } else {
                            set.status = set.status === 1 ? 0 : 1;
                        }
                    } catch (err) {
                        console.error(err);
                        window.dispatchEvent(new CustomEvent('notice', {
                            detail: { type: 'error', text: 'Failed to toggle status.' }
                        }));
                    } finally {
                        set._loading = false;
                    }
                },

                // question sets filter
                filters: {
                    showActive: true,
                    showDisabled: true,
                    sortTopic: null,
                    sortTime: 'end_asc',
                },

                get filteredQuestionSets() {
                    let sets = [...this.questionSets];

                    sets = sets.filter(set =>
                        (this.filters.showActive && set.status === 1) ||
                        (this.filters.showDisabled && set.status === 0)
                    );

                    if (this.filters.sortTopic === 'asc') {
                        sets.sort((a, b) => a.topic.localeCompare(b.topic));
                    } else if (this.filters.sortTopic === 'desc') {
                        sets.sort((a, b) => b.topic.localeCompare(a.topic));
                    }

                    if (this.filters.sortTime) {
                        if (this.filters.sortTime === 'start_asc') {
                            sets.sort((a, b) => new Date(a.start_time) - new Date(b.start_time));
                        } else if (this.filters.sortTime === 'start_desc') {
                            sets.sort((a, b) => new Date(b.start_time) - new Date(a.start_time));
                        } else if (this.filters.sortTime === 'end_asc') {
                            sets.sort((a, b) => new Date(a.end_time) - new Date(b.end_time));
                        } else if (this.filters.sortTime === 'end_desc') {
                            sets.sort((a, b) => new Date(b.end_time) - new Date(a.end_time));
                        }
                    }

                    return sets;
                },

                // student search part
                studentSearch: '',
                debouncedSearch: '',
                debounceTimer: null,

                updateSearch() {
                    clearTimeout(this.debounceTimer);
                    this.debounceTimer = setTimeout(() => {
                        this.debouncedSearch = this.studentSearch.toLowerCase();
                    }, 500); // wait 0.5s 
                },

                get filteredStudents() {
                    if (!this.debouncedSearch) return this.students;
                    return this.students.filter(s =>
                        (s.name ?? '').toLowerCase().includes(this.debouncedSearch)
                    );
                },
            };
        }
    </script>
</x-teacher>