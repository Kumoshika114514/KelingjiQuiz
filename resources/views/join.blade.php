<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Join a Class') }}
        </h2>
    </x-slot>

    <div class="py-12 flex justify-center">
        <div class="w-full max-w-md bg-white p-8 rounded-xl shadow-lg space-y-6">
            <!-- Tab Navigation - Centered -->
            <div class="flex justify-center space-x-8 border-b-2 pb-4">
                <a href="#join-class" class="text-lg font-medium text-gray-700 border-b-2 border-blue-500 pb-2">Join Class</a>
            </div>

            <!-- Class Code Input Section -->
            <form method="POST" action="{{ route('studentclasses.store') }}">
                @csrf
                <div id="join-class" class="space-y-4">
                    <label for="class_code" class="text-sm font-medium text-gray-700">Enter the class code provided by your teacher</label>
                    <input id="class_code" type="text" name="class_code" class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500" placeholder="Class Code" required>

                    <!-- Add margin-top to space out the input from the button -->
                    <div class="flex flex-col gap-4 mt-6">
                        <!-- Find Your Class Button -->
                        <button type="submit" class="w-full py-3 bg-blue-500 text-white rounded-lg shadow-md hover:bg-blue-600 focus:ring-4 focus:ring-blue-500 transition duration-200">
                            Find your Class
                        </button>
                        
                        <!-- Cancel Button -->
                        <button type="button" class="w-full py-3 bg-white text-gray-700 border border-gray-300 rounded-lg shadow-md hover:bg-gray-100 focus:ring-4 focus:ring-gray-500 transition duration-200">
                            Cancel
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
