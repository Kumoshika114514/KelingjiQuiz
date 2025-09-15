{{-- filepath: c:\xampp\htdocs\KelingjiQuiz\resources\views\student\viewClass.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ $class->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Class Description Section -->
            <div class="bg-white shadow-sm rounded-lg p-6 mb-6">
                <h3 class="font-semibold text-lg">Class Description:</h3>
                <p class="text-gray-700">{{ $class->description }}</p>
            </div>

            <!-- Available Quizzes Section -->
            <div class="bg-white shadow-sm rounded-lg p-6 mb-6">
                <h3 class="font-semibold text-lg mt-4 text-green-700">Available Quizzes</h3>
                @if($availableQuizzes->isEmpty())
                    <p class="text-gray-700 mt-4">No available quizzes for this class.</p>
                @else
                    <ul class="space-y-4 mt-4">
                        @foreach($availableQuizzes as $questionSet)
                            <li class="border p-4 rounded-lg hover:bg-gray-100 transition">
                                <a href="{{ route('student.quizzes.takeQuiz', $questionSet->id) }}" class="block">
                                    <h4 class="text-xl font-semibold text-blue-600">{{ $questionSet->topic }}</h4>
                                    <p class="text-gray-600 mt-2">{{ $questionSet->description }}</p>
                                    <div class="text-sm text-gray-500 mt-2">
                                        <p>Number of Questions: <strong>{{ $questionSet->questions->count() }}</strong></p>
                                        <p>Time Limit: <strong>{{ $questionSet->answer_time }} minutes</strong></p>
                                    </div>
                                    <div class="mt-2">
                                        <span class="inline-block bg-green-500 text-white py-1 px-3 rounded-full text-xs">Available</span>
                                    </div>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>

            <!-- Completed Quizzes Section -->
            <div class="bg-white shadow-sm rounded-lg p-6">
                <h3 class="font-semibold text-lg mt-4 text-blue-700">Completed Quizzes</h3>
                @if($completedQuizzes->isEmpty())
                    <p class="text-gray-700 mt-4">No completed quizzes for this class.</p>
                @else
                    <ul class="space-y-4 mt-4">
                        @foreach($completedQuizzes as $questionSet)
                            <li class="border p-4 rounded-lg bg-gray-50 flex flex-col gap-2">
                                <div>
                                    <a href="{{ route('student.quizzes.summary', $questionSet->id) }}" class="block hover:underline mb-2">
                                        <h4 class="text-xl font-semibold text-blue-600">{{ $questionSet->topic }}</h4>
                                        <p class="text-gray-600 mt-2">{{ $questionSet->description }}</p>
                                        <div class="text-sm text-gray-500 mt-2">
                                            <p>Number of Questions: <strong>{{ $questionSet->questions->count() }}</strong></p>
                                            <p>Time Limit: <strong>{{ $questionSet->answer_time }} minutes</strong></p>
                                        </div>
                                    </a>
                                </div>
                                <div class="mt-2 flex items-center gap-2">
                                    <span class="inline-block bg-blue-500 text-white py-1 px-3 rounded-full text-xs">Completed</span>
                                    <a href="{{ route('student.quizzes.takeQuiz', $questionSet->id) }}"
                                        class="inline-block bg-blue-600 hover:bg-blue-800 text-white font-semibold py-1 px-3 rounded text-xs ml-2 shadow border border-blue-800">
                                        Retake Quiz
                                    </a>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>