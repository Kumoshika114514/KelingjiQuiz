<x-teacher>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-2xl text-gray-800 leading-tight">
                {{ __('My Classes') }}
            </h2>
            <a href="{{ route('quizclasses.create') }}"
                class="bg-blue-600 hover:bg-blue-700 dark:text-white font-medium py-2 px-4 rounded-lg shadow transition">
                Create Class
            </a>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 mt-6">
            <div x-data="{ quizClasses: [] }" x-init="fetch('/api/teacher/dashboard')
                .then(res => res.json())
                .then(data => quizClasses = data.classes)" class="space-y-6">

                <!-- If no result found -->
                <template x-if="quizClasses.length === 0">
                    <div class="bg-white shadow rounded-lg p-6 text-center">
                        <p class="text-gray-600">You don't have any classes yet.</p>
                    </div>
                </template>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6" x-show="quizClasses.length > 0"
                    x-cloak>
                    <template x-for="cls in quizClasses" :key="cls.id">
                        <a :href="`/teacher/quizclass/${cls.id}`"
                            class="block bg-white rounded-xl shadow hover:shadow-lg transition transform hover:-translate-y-1 p-6">

                            <div class="flex justify-between items-center mb-3">
                                <h3 class="text-lg font-semibold text-gray-900" x-text="cls.name"></h3>
                                <span class="text-xs px-2 py-1 bg-green-100 text-green-700 rounded-full">
                                    Active
                                </span>
                            </div>

                            <p class="text-sm text-gray-600">
                                Created on
                                <span x-text="new Date(cls.created_at).toLocaleDateString()"></span>
                            </p>

                            <div class="mt-4 flex justify-between items-center">
                                <span class="text-blue-600 text-sm font-medium hover:underline">View Details →</span>
                            </div>
                        </a>
                    </template>
                </div>
            </div>
        </div>
    </div>
</x-teacher>