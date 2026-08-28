<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <title>{{ $title ?? config('app.name') }}</title>

        @vite(['resources/css/app.css', 'resources/js/app.js'])

        @livewireStyles
    </head>
    <body class="bg-background text-foreground antialiased">
        <button type="button" x-on:click="toggleTheme()"
            class="fixed right-4 top-4 z-50 inline-flex h-10 w-10 items-center justify-center rounded-full border border-border bg-card/90 text-foreground shadow-sm backdrop-blur-sm transition hover:bg-muted focus:outline-none focus:ring-2 focus:ring-primary/40"
            aria-label="Basculer le thème">
            <i data-lucide="sun" class="h-4 w-4 hidden dark:block"></i>
            <i data-lucide="moon" class="h-4 w-4 block dark:hidden"></i>
        </button>

        @yield('content')

        @livewireScripts
    </body>
</html>