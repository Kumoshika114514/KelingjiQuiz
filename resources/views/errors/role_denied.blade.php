<x-app-layout>
    <div class="flex flex-col items-center justify-center min-h-screen bg-gray-100 dark:bg-gray-900 px-4">
        <h1 class="text-xl font-extrabold text-red-600 mb-4">ACCESS DENIED</h1>

        <p class="mt-2 text-lg text-gray-700 dark:text-gray-300 text-center">
            You tried to access a page that requires
            <span class="font-semibold uppercase">{{ $expectedRole ?? '' }}</span>,
            but your account role is
            <span class="font-semibold uppercase">{{ $actualRole ?? '' }}</span>.
        </p>

        <div class="mt-6">
            <a href="{{ route((strtolower($actualRole ?? '') === 'teacher') ? 'teacher.dashboard' : 'dashboard') }}"
               class="inline-block bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                Go Back to Dashboard
            </a>
        </div>
    </div>
</x-app-layout>
