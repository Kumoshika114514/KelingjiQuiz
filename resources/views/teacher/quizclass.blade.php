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
            <div class="bg-white rounded-b-lg border border-t-0 shadow-sm p-6 mt-0 dark:bg-gray-800 dark:border-gray-700">

                <!-- question sets part (load by default) -->
                <div x-show="active === 'questions'" x-transition>
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-300">Question Sets</h3>
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

                            <!-- Card -->
                            <template x-for="set in questionSets" :key="set.id">
                                <div class="flex items-start space-x-3">
                                    <!-- Clickable content -->
                                    <a :href="`/teacher/quizclass/${quizClassId}/questionsets/${set.id}`"
                                       class="flex-1 block p-4 rounded-lg border transition hover:shadow-md"
                                       :class="cardClass(stateOf(set))">
                                        <div class="flex items-center justify-between">
                                            <div class="min-w-0">
                                                <div class="font-semibold truncate"
                                                     :class="titleClass(stateOf(set))"
                                                     x-text="set.topic"></div>

                                                <div class="text-sm text-gray-500 mt-1">
                                                    Type: <span x-text="formatType(set.question_type)"></span>
                                                </div>
                                                <div class="text-sm text-gray-500 mt-1">
                                                    Highest Score:
                                                    <span x-text="set.highest_score !== null ? formatType(set.highest_score) : '-'"></span>
                                                </div>
                                            </div>

                                            <div class="ml-4 shrink-0 text-right">
                                                <!-- State badge -->
                                                <span class="px-2 py-0.5 text-xs rounded-full font-medium"
                                                      :class="badgeClass(stateOf(set))"
                                                      x-text="prettyState(stateOf(set))"></span>
                                            </div>
                                        </div>
                                    </a>

                                    <!-- Actions (outside the link) -->
                                    <div class="mt-1 shrink-0 space-y-2">
                                        <!-- ACTIVE -> Disable (Close) -->
                                        <template x-if="stateOf(set) === 'ACTIVE'">
                                            <form method="POST"
                                                  :action="`/teacher/quizclass/${quizClassId}/questionsets/${set.id}/disable`">
                                                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                                <button type="submit"
                                                        class="rounded border px-3 py-2 text-sm hover:bg-gray-50">
                                                    Disable
                                                </button>
                                            </form>
                                        </template>

                                        <!-- SCHEDULED -> Activate / Back to Draft -->
                                        <template x-if="stateOf(set) === 'SCHEDULED'">
                                            <div class="space-y-2">
                                                <form method="POST"
                                                      :action="`/teacher/quizclass/${quizClassId}/questionsets/${set.id}/activate`">
                                                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                                    <button type="submit"
                                                            class="w-full rounded bg-indigo-600 px-3 py-2 text-sm text-white hover:bg-indigo-700">
                                                        Activate
                                                    </button>
                                                </form>
                                                <form method="POST"
                                                      :action="`/teacher/quizclass/${quizClassId}/questionsets/${set.id}/disable`">
                                                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                                    <button type="submit"
                                                            class="w-full rounded border px-3 py-2 text-sm hover:bg-gray-50">
                                                        Back to Draft
                                                    </button>
                                                </form>
                                            </div>
                                        </template>

                                        <!-- CLOSED -> Activate / Archive -->
                                        <template x-if="stateOf(set) === 'CLOSED'">
                                            <div class="space-y-2">
                                                <form method="POST"
                                                      :action="`/teacher/quizclass/${quizClassId}/questionsets/${set.id}/activate`">
                                                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                                    <button type="submit"
                                                            class="w-full rounded bg-indigo-600 px-3 py-2 text-sm text-white hover:bg-indigo-700">
                                                        Activate
                                                    </button>
                                                </form>
                                                <form method="POST"
                                                      :action="`/teacher/quizclass/${quizClassId}/questionsets/${set.id}/archive`">
                                                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                                    <button type="submit"
                                                            class="w-full rounded border px-3 py-2 text-sm hover:bg-gray-50">
                                                        Archive
                                                    </button>
                                                </form>
                                            </div>
                                        </template>

                                        <!-- DRAFT -> Activate / Archive -->
                                        <template x-if="stateOf(set) === 'DRAFT'">
                                        <div class="space-y-2">
                                            <form method="POST"
                                                :action="`/teacher/quizclass/${quizClassId}/questionsets/${set.id}/activate`">
                                            <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                            <button type="submit"
                                                    class="w-full rounded bg-indigo-600 px-3 py-2 text-sm text-white hover:bg-indigo-700">
                                                Activate
                                            </button>
                                            </form>

                                            <form method="POST"
                                                :action="`/teacher/quizclass/${quizClassId}/questionsets/${set.id}/archive`">
                                            <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                            <button type="submit"
                                                    class="w-full rounded border px-3 py-2 text-sm hover:bg-gray-50">
                                                Archive
                                            </button>
                                            </form>
                                        </div>
                                        </template>
                                        <!-- ARCHIVED -> Restore (maps to POST disable) -->
                                        <template x-if="stateOf(set) === 'ARCHIVED'">
                                        <form method="POST" :action="`/teacher/quizclass/${quizClassId}/questionsets/${set.id}/disable`">
                                            <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                            <button type="submit" class="rounded border px-3 py-2 text-sm hover:bg-gray-50">
                                                Restore
                                            </button>
                                        </form>
                                        </template>

                                    </div>
                                </div>
                            </template>

                        </div>
                    </template>
                </div>

                <!-- students part (lazy loaded) -->
                <div x-show="active === 'students'" x-transition>
                    <h3 class="text-lg font-semibold mb-3 text-gray-800 dark:text-gray-200">Students Joined</h3>
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
                                        <div class="font-medium text-gray-800 dark:text-gray-300" x-text="student.name"></div>
                                        <div class="text-sm text-gray-500 dark:text-gray-400" x-text="student.email"></div>
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

                /* ---------- STATE HELPERS ---------- */
                stateOf(set) {
                    // Prefer explicit backend state; fallback to old status flag
                    const s = (set.state || '').toString().toUpperCase();
                    if (s) return s;
                    return set.status === 1 ? 'ACTIVE' : 'CLOSED';
                },
                prettyState(s) {
                    return s.charAt(0) + s.slice(1).toLowerCase();
                },
                badgeClass(s) {
                    switch (s) {
                        case 'ACTIVE': return 'bg-green-100 text-green-800';
                        case 'SCHEDULED': return 'bg-yellow-100 text-yellow-700';
                        case 'CLOSED': return 'bg-red-100 text-red-700';
                        case 'ARCHIVED': return 'bg-slate-200 text-slate-700';
                        case 'DRAFT': return 'bg-gray-200 text-gray-800';
                        default: return 'bg-gray-200 text-gray-800'; 
                    }
                },
                titleClass(s) {
                    switch (s) {
                        case 'ACTIVE': return 'text-green-800';
                        case 'CLOSED': return 'text-red-800';
                        default: return 'text-gray-900';
                    }
                },
                cardClass(s) {
                    switch (s) {
                        case 'ACTIVE': return 'bg-white border-green-200';
                        case 'SCHEDULED': return 'bg-white border-yellow-200';
                        case 'CLOSED': return 'bg-gray-50 border-red-200';
                        case 'ARCHIVED': return 'bg-gray-50 border-slate-200 opacity-90';
                        default: return 'bg-white border-gray-200';
                    }
                },

                /* ---------- (Legacy) Toggle kept for API compatibility, unused by new buttons ---------- */
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

                        // update either new state or legacy status
                        if (typeof data.state === 'string') {
                            set.state = data.state;
                        } else if (typeof data.status !== 'undefined') {
                            set.status = data.status;
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
            };
        }
    </script>

</x-teacher>
