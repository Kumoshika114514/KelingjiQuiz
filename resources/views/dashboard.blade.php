<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-white leading-tight">
                {{ __('Dashboard') }}
            </h2>
            <a href="{{ route('studentclasses.join') }}"
                class="bg-blue-600 hover:bg-blue-700 text-white py-2 px-4 rounded-lg shadow text-base transition">
                Join Class
            </a>
        </div>
    </x-slot>

    <div class="py-12 bg-gray-100 min-h-screen">
        <div class="max-w-7xl mx-auto px-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-16">
                @forelse ($quizClasses as $class)
                    <a href="{{ route('student.viewClass', $class->id) }}"
                        class="group block rounded-xl shadow-lg bg-white hover:shadow-2xl transition overflow-hidden border border-gray-200">
                        <!-- Colored header bar -->
                        <div class="h-3 w-full" style="background: linear-gradient(90deg, #4f8cff, #38b2ac);"></div>
                        <div class="p-6 flex flex-col h-full">
                            <div class="flex-1">
                                <div class="flex items-center justify-between">
                                    <span class="text-lg font-bold text-gray-800 group-hover:text-blue-700 transition">{{ $class->name }}</span>
                                    <span class="text-xs text-gray-400">{{ $class->created_at->diffForHumans() }}</span>
                                </div>
                                <p class="text-gray-600 mt-2 mb-4">{{ Str::limit($class->description, 60, '...') }}</p>
                            </div>
                            <!--<div class="flex items-center justify-between mt-auto">
                                <span class="inline-block bg-green-100 text-green-700 px-3 py-1 rounded-full text-xs font-semibold">
                                    Quizzes Available: {{ $class->available_quizzes_count }}
                                </span>
                                <span class="text-gray-400 group-hover:text-blue-600 text-xl transition">&#8594;</span>
                            </div>-->
                        </div>
                    </a>
                @empty
                    <div class="bg-white p-6 rounded-lg shadow text-center col-span-full">
                        <p>You haven't joined any class yet. Click 'Join Class' to join a class and start your quiz!</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</x-app-layout>