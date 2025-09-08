<x-guest-layout>
    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}">
        @csrf

        @if($errors->any())
            <div class="mb-4 text-red-600">
                <ul>
                    @foreach($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required
                autofocus autocomplete="username" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" />

            <x-text-input id="password" class="block mt-1 w-full" type="password" name="password" required
                autocomplete="current-password" />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Role Selection -->
        <div class="mt-4">
            <x-input-label class="block text-sm font-medium mb-1">Sign in as: </x-input-label>
            <div class="flex items-center gap-4">
                <label class="inline-flex items-center">
                    <input type="radio" name="role" value="student" class="form-radio" checked>
                    <span class="ml-2 text-gray-700 dark:text-gray-200">Student</span>
                </label>

                <label class="inline-flex items-center">
                    <input type="radio" name="role" value="teacher" class="form-radio">
                    <span class="ml-2 text-gray-700 dark:text-gray-200">Teacher</span>
                </label>
            </div>
        </div>


        <!-- Font Size Selection -->
        <div class="mt-4">
            <x-input-label for="font_select" class="block text-sm font-medium mb-1">Text Size</x-input-label>

            <div class="relative inline-block w-full sm:w-56">
                <select id="font_select" class="appearance-none w-full rounded-md border px-3 py-2 pr-10
                   bg-white dark:bg-gray-800 text-sm text-gray-700 dark:text-gray-200
                   shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                    aria-label="Select text size">
                    <option value="sm">Small</option>
                    <option value="md">Medium</option>
                    <option value="lg">Large</option>
                </select>

            </div>
        </div>

        <!-- Theme Selection -->
        <div class="mt-4">
            <x-input-label class="block text-sm font-medium mb-1">Theme: </x-input-label>
            <div class="flex items-center gap-4">
                <label class="inline-flex items-center">
                    <input type="radio" name="theme" value="light" class="form-radio" checked>
                    <span class="ml-2 text-gray-700 dark:text-gray-200">Light</span>
                </label>

                <label class="inline-flex items-center">
                    <input type="radio" name="theme" value="dark" class="form-radio">
                    <span class="ml-2 text-gray-700 dark:text-gray-200">Dark</span>
                </label>
            </div>
        </div>


        <input type="hidden" name="font_size" id="font_input" value="md">
        <input type="hidden" name="theme" id="theme_input" value="light">

        <!-- Remember Me -->
        <div class="block mt-4">
            <label for="remember_me" class="inline-flex items-center">
                <input id="remember_me" type="checkbox"
                    class="rounded dark:bg-gray-900 border-gray-300 dark:border-gray-700 text-indigo-600 shadow-sm focus:ring-indigo-500 dark:focus:ring-indigo-600 dark:focus:ring-offset-gray-800"
                    name="remember">
                <span class="ms-2 text-sm text-gray-600 dark:text-gray-400">{{ __('Remember me') }}</span>
            </label>
        </div>

        <div class="flex items-center justify-end mt-4">
            @if (Route::has('password.request'))
                <a class="underline text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800"
                    href="{{ route('password.request') }}">
                    {{ __('Forgot your password?') }}
                </a>
            @endif

            <x-primary-button class="ms-3">
                {{ __('Log in') }}
            </x-primary-button>
        </div>

        @if (Route::has('register'))
            <div class="mt-4 text-center">
                <span class="text-sm text-gray-600 dark:text-gray-400">Don't have an account?</span>
                <a href="{{ route('register') }}"
                    class="ml-2 text-sm font-medium text-indigo-600 hover:text-indigo-700 dark:text-indigo-400 dark:hover:text-indigo-300">
                    Create one here!
                </a>
            </div>
        @endif
    </form>

    <script>
        (function () {
            const themeRadios = document.querySelectorAll('input[name="theme"]');
            const themeInput = document.getElementById('theme_input');

            const select = document.getElementById('font_select');
            const fontInput = document.getElementById('font_input');

            // helper cookie functions
            function setCookie(name, value, days = 365) {
                const d = new Date();
                d.setTime(d.getTime() + days * 24 * 60 * 60 * 1000);
                document.cookie = `${name}=${encodeURIComponent(value)};path=/;expires=${d.toUTCString()};SameSite=Lax`;
            }
            function getCookie(name) {
                const m = document.cookie.match(new RegExp('(^| )' + name + '=([^;]+)'));
                return m ? decodeURIComponent(m[2]) : null;
            }

            // apply theme preview (toggles dark class on html)
            function applyTheme(theme) {
                if (theme === 'dark') document.documentElement.classList.add('dark');
                else document.documentElement.classList.remove('dark');
            }

            // apply font-size preview (applies wrapper class)
            function applyFontPreview(font) {
                const wrapper = document.querySelector('div.min-h-screen');
                if (!wrapper) return;
                wrapper.classList.remove('app-font-sm', 'app-font-md', 'app-font-lg');
                if (font === 'sm') wrapper.classList.add('app-font-sm');
                else if (font === 'lg') wrapper.classList.add('app-font-lg');
                else wrapper.classList.add('app-font-md');
            }

            // initialize controls from session-shared Blade variables for cookies
            const initialTheme = '{{ $theme ?? "light" }}' || getCookie('theme') || 'light';
            const initialFont = '{{ $font_size ?? "md" }}' || getCookie('font_size') || 'md';

            // set radios according to initialTheme
            themeRadios.forEach(r => { if (r.value === initialTheme) r.checked = true; });
            themeInput.value = initialTheme;
            applyTheme(initialTheme);

            // set font select
            if (select) {
                select.value = initialFont;
                fontInput.value = initialFont;
                applyFontPreview(initialFont);
            }

            // event listeners
            themeRadios.forEach(r => {
                r.addEventListener('change', () => {
                    const v = document.querySelector('input[name="theme"]:checked').value;
                    themeInput.value = v;
                    setCookie('theme', v);
                    applyTheme(v);
                });
            });

            if (select) {
                select.addEventListener('change', () => {
                    const v = select.value;
                    fontInput.value = v;
                    setCookie('font_size', v);
                    applyFontPreview(v);
                });
            }
        })();
    </script>
</x-guest-layout>