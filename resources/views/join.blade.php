<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Join Class') }}
            </h2>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <form method="POST" action="{{ route('studentclasses.store') }}">
                @csrf
                <div>
                    <label class="block text-white">Class Code:</label>
                    <input type="text" name="class_code" class="w-full rounded border p-2" required>
                </div>
                <div class="mt-4">
                    <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">Join</button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>