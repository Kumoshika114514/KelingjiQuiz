<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" 
class="{{ session('theme','light') === 'dark' ? 'dark' : '' }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Kelingji Quiz') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="//unpkg.com/alpinejs" defer>
        (function () {
            let theme = '{{ session('theme', 'light') }}';
            if (!theme || theme === 'null') {
                // fallback to localStorage if session not set
                theme = localStorage.getItem('color-theme') || 'light';
            }

            if (theme === 'dark') {
                document.documentElement.classList.add('dark');
            } else {
                document.documentElement.classList.remove('dark');   
            }

            try { document.documentElement.setAttribute('data-theme-initialized', '1'); } catch (e) {}
  }
        )();
    </script> <!-- Alpine.js -->
</head>

<body class="font-sans antialiased">
    <!-- Pop Up Message Box-->
    <div class="fixed top-0 inset-x-0 flex justify-center z-50">
        <div class="w-full max-w-md mt-4 space-y-2">
            @if (session('success'))
                <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 3000)" x-show="show"
                    x-transition:enter="transition ease-out duration-500"
                    x-transition:enter-start="-translate-y-full opacity-0"
                    x-transition:enter-end="translate-y-0 opacity-100" x-transition:leave="transition ease-in duration-500"
                    x-transition:leave-start="translate-y-0 opacity-100"
                    x-transition:leave-end="-translate-y-full opacity-0"
                    class="bg-green-500 dark:text-white px-6 py-3 rounded shadow-lg text-center">
                    {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 3000)" x-show="show"
                    x-transition:enter="transition ease-out duration-500"
                    x-transition:enter-start="-translate-y-full opacity-0"
                    x-transition:enter-end="translate-y-0 opacity-100" x-transition:leave="transition ease-in duration-500"
                    x-transition:leave-start="translate-y-0 opacity-100"
                    x-transition:leave-end="-translate-y-full opacity-0"
                    class="bg-red-500 dark:text-white px-6 py-3 rounded shadow-lg text-center">
                    {{ session('error') }}
                </div>
            @endif
        </div>
    </div>

    <div class="min-h-screen bg-gray-100 dark:bg-gray-900">
        @include('layouts.navigation')

        <!-- Page Heading -->
        @isset($header)
            <header class="bg-white dark:bg-gray-800 shadow">
                <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                    {{ $header }}
                </div>
            </header>
        @endisset

        <!-- Page Content -->
        <main>
            {{ $slot }}
        </main>
    </div>
</body>

</html>