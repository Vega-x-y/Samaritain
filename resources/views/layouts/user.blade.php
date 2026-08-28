<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <title>{{ $title ?? config('app.name') }}</title>

        <script>
            (function () {
                try {
                    const storedTheme = localStorage.getItem('theme');
                    const prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
                    const shouldUseDarkTheme = storedTheme ? storedTheme === 'dark' : prefersDark;

                    document.documentElement.classList.toggle('dark', shouldUseDarkTheme);
                } catch (e) {
                    document.documentElement.classList.remove('dark');
                }
            })();
        </script>

        @vite(['resources/css/app.css', 'resources/js/app.js'])

        @livewireStyles
        @fluxAppearance
    </head>
    <body class="bg-background text-foreground antialiased">
        <button id="theme-toggle" type="button"
            class="fixed right-4 top-4 z-50 inline-flex h-10 w-10 items-center justify-center rounded-full border border-border bg-card/90 text-foreground shadow-sm backdrop-blur-sm transition hover:bg-muted focus:outline-none focus:ring-2 focus:ring-primary/40"
            aria-label="Basculer le thème">
            <i data-lucide="sun" class="h-4 w-4 hidden dark:block"></i>
            <i data-lucide="moon" class="h-4 w-4 block dark:hidden"></i>
        </button>

        @yield('content')

        <script>
            function toggleTheme() {
                const isDark = document.documentElement.classList.contains('dark');
                if (isDark) {
                    document.documentElement.classList.remove('dark');
                    localStorage.setItem('theme', 'light');
                } else {
                    document.documentElement.classList.add('dark');
                    localStorage.setItem('theme', 'dark');
                }
            }

            document.addEventListener('DOMContentLoaded', function () {
                const toggle = document.getElementById('theme-toggle');
                if (toggle) {
                    toggle.addEventListener('click', toggleTheme);
                }
            });
        </script>

        @livewireScripts
        @fluxScripts
    </body>
</html>