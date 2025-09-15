<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-white leading-tight">
                {{ __('Dashboard') }}
            </h2>
            <a href="{{ route('studentclasses.join') }}"
                class=" hover:bg-blue-700 text-white py-2 px-4 rounded-lg shadow text-base">
                Join Class
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                @forelse ($quizClasses as $class)
                    <!-- Modern Google Classroom Inspired Card Design -->
                    <div class="mb-4">
                        <a href="{{ route('student.viewClass', $class->id) }}"
                            class="block bg-white text-gray-900 p-6 rounded-lg shadow-lg hover:shadow-xl transition-shadow duration-300 hover:bg-gray-100">
                            <div class="flex items-center justify-between">
                                <!-- Class Name -->
                                <p class="text-xl font-semibold">{{ $class->name }}</p>
                                <span class="text-sm text-gray-500">{{ $class->created_at->diffForHumans() }}</span>
                            </div>

                            <!-- Class Description -->
                            <p class="text-gray-600 mt-2">{{ Str::limit($class->description, 80, '...') }}</p>

                            <!-- Optional: Add more content like number of quizzes -->
                            <div class="mt-4">
                                <span class="text-sm text-gray-500">Quizzes Available: {{ $class->available_quizzes_count }}
                                    <!--{{
                                        $class->questionSets
                                        ->where('start_time', '<=', now())
                                        ->where('end_time', '>=', now())
                                        ->where('status', 1)
                                        ->where('is_active', 1)
                                        ->filter(function($quiz) {
                                            $questionIds = $quiz->questions->pluck('id');
                                            $answered = \App\Models\StudentAnswer::where('user_id', Auth::id())
                                                ->whereIn('question_id', $questionIds)
                                                ->pluck('question_id')
                                                ->unique();
                                            return $questionIds->count() > 0 && !$questionIds->diff($answered)->isEmpty();
                                        })->count()
                                    }}-->
                                </span>
                            </div>
                        </a>
                    </div>
                @empty
                    <p>You haven't joined any class yet. Click 'Join Class' to join a class and start your quiz!</p>
                @endforelse
            </div>
        </div>
    </div>
</x-app-layout>