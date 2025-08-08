<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
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
                    <div>
                        <a href="">
                            <p>{{ $class->name }}</p>
                        </a>
                    </div>
                @empty
                    <p>You haven't join any class yet. Click 'Join Class' to join a class and start your quiz!</p>
                @endforelse
            </div>
        </div>
    </div>
</x-app-layout>