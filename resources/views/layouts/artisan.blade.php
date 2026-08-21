<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title') | Dashboard Artisan - Samaritain</title>

    <script>
        (function() {
            if (localStorage.theme === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
                document.documentElement.classList.add('dark');
            } else {
                document.documentElement.classList.remove('dark');
            }
        })();
    </script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles

    <style>
        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }

        ::-webkit-scrollbar-track {
            background: transparent;
        }

        ::-webkit-scrollbar-thumb {
            background: var(--muted);
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: var(--muted-foreground);
        }
    </style>
</head>

@php
    /** @var \App\Models\Artisan|null $artisan */
    $artisan = $artisan ?? optional(auth()->user())->artisan;
    $stats = $stats ?? [];
    $unreadContacts = $stats['contacts_count'] ?? 0;
    $pendingVerification = $artisan && ! $artisan->verified;
    $messagesNonLus = $messagesNonLus ?? ($artisan
        ? \App\Models\Message::whereHas('conversation', fn ($q) => $q->where('artisan_id', $artisan->id))
            ->where('lu', false)
            ->where('expediteur_type', '!=', 'artisan')
            ->count()
        : 0);
@endphp

<body x-data="{
    sidebarOpen: true,
    mobileMenuOpen: false,
    toggleSidebar() {
        if (window.innerWidth < 768) {
            this.mobileMenuOpen = !this.mobileMenuOpen;
        } else {
            this.sidebarOpen = !this.sidebarOpen;
        }
    }
}" class="bg-background text-foreground font-sans antialiased h-screen flex overflow-hidden">

    <!-- Mobile Drawer Navigation Sheet -->
    <div x-show="mobileMenuOpen" class="relative z-50 md:hidden" role="dialog" aria-modal="true" style="display: none;">
        <div x-show="mobileMenuOpen" x-transition:enter="transition-opacity ease-linear duration-300"
            x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
            x-transition:leave="transition-opacity ease-linear duration-300" x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0" x-on:click="mobileMenuOpen = false"
            class="fixed inset-0 bg-black/80 backdrop-blur-md transition-opacity"></div>

        <div class="fixed inset-y-0 left-0 flex max-w-full">
            <div x-show="mobileMenuOpen" x-transition:enter="transition ease-in-out duration-300 transform"
                x-transition:enter-start="-translate-x-full" x-transition:enter-end="translate-x-0"
                x-transition:leave="transition ease-in-out duration-300 transform"
                x-transition:leave-start="translate-x-0" x-transition:leave-end="-translate-x-full"
                class="w-64 bg-sidebar flex flex-col h-full border-r border-sidebar-border relative shadow-2xl transition-transform">

                <div class="absolute top-3.5 right-3 z-50">
                    <button x-on:click="mobileMenuOpen = false"
                        class="p-1 rounded-md text-sidebar-foreground hover:text-foreground hover:bg-sidebar-border transition-colors focus:outline-none">
                        <i data-lucide="x" height="16" width="16"></i>
                    </button>
                </div>

                <div class="h-full flex flex-col">
                    @include('layouts.partials.artisan-sidebar')
                </div>
            </div>
        </div>
    </div>

    <!-- Desktop Sidebar Wrapper -->
    <div class="hidden md:block shrink-0">
        @include('layouts.partials.artisan-sidebar')
    </div>

    <!-- Main Content Area Wrapper -->
    <div class="flex-1 flex flex-col min-h-0 overflow-hidden transition-all duration-300">

        <!-- Header / Top-bar -->
        <header class="h-14 border-b border-sidebar-border flex items-center gap-2 px-2 sm:px-4 justify-between shrink-0 bg-sidebar">
            <div class="flex items-center gap-2 min-w-0">
                <!-- Sidebar Toggle Button -->
                <button x-on:click="toggleSidebar()"
                    class="p-1.5 rounded-md text-sidebar-foreground hover:text-foreground hover:bg-accent transition-colors"
                    aria-label="Toggle Sidebar">
                    <i data-lucide="panel-left" height="16" width="16"></i>
                </button>

                <!-- Divider -->
                <div class="w-px h-4 bg-sidebar-border mx-2"></div>

                <!-- Breadcrumbs -->
                @yield('breadcrumbs')
            </div>

            <!-- Right-side actions -->
            <div class="flex items-center gap-1 sm:gap-3 shrink-0">
                <!-- Dynamic Badges (powered by controller) -->
                @if ($pendingVerification)
                    <div class="flex items-center gap-1.5 px-2.5 py-1 rounded-md bg-amber-100 dark:bg-amber-900/30 text-amber-800 dark:text-amber-300">
                        <i data-lucide="clock" class="w-3.5 h-3.5"></i>
                        <span class="hidden sm:inline text-xs font-medium">En attente de validation</span>
                    </div>
                @endif

                <!-- Theme Toggle -->
                <button x-data="{
                    toggleTheme() {
                        const isDark = document.documentElement.classList.contains('dark');
                        if (isDark) {
                            document.documentElement.classList.remove('dark');
                            localStorage.theme = 'light';
                        } else {
                            document.documentElement.classList.add('dark');
                            localStorage.theme = 'dark';
                        }
                    }
                }" x-on:click="toggleTheme()"
                    class="p-1.5 text-muted-foreground hover:text-foreground rounded-md hover:bg-accent">
                    <i data-lucide="sun" class="h-4 w-4 hidden dark:block"></i>
                    <i data-lucide="moon" class="h-4 w-4 block dark:hidden"></i>
                </button>

                <!-- User Profile -->
                <div class="relative" x-data="{ open: false }">
                    <button x-on:click="open = !open" x-on:click.away="open = false"
                        class="flex items-center gap-2 p-1.5 rounded-md text-sidebar-foreground hover:text-foreground hover:bg-accent transition-colors">
                        @if (auth()->user()->profileUrl())
                            <img src="{{ auth()->user()->profileUrl() }}" alt="{{ auth()->user()->name }}"
                                class="w-7 h-7 rounded-md object-cover border border-sidebar-border">
                        @else
                            <div class="w-7 h-7 rounded-md bg-zinc-700 flex items-center justify-center text-xs font-medium text-white">
                                {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                            </div>
                        @endif
                        <span x-show="sidebarOpen" class="hidden sm:inline text-sm font-medium truncate max-w-32"
                            x-cloak>{{ auth()->user()->name }}</span>
                        <i data-lucide="chevrons-up-down" class="h-4 w-4 shrink-0"></i>
                    </button>

                    <div x-show="open" x-on:click.away="open = false"
                        class="origin-bottom-right absolute right-0 bottom-full mb-2 w-48 bg-sidebar border border-sidebar-border rounded-md shadow-lg z-50 overflow-hidden flex flex-col gap-1 p-1"
                        x-cloak>
                        <a href="{{ route('profile.show') }}"
                            class="flex items-center gap-2 text-sm text-sidebar-foreground hover:text-foreground hover:bg-sidebar-border px-2 py-2 rounded-md transition-colors">
                            <i data-lucide="user" class="w-4 h-4"></i>
                            Profil
                        </a>
                        @if ($artisan && auth()->id() === $artisan->user_id)
                            <a href="{{ route('artisan.edit', $artisan) }}"
                                class="flex items-center gap-2 text-sm text-sidebar-foreground hover:text-foreground hover:bg-sidebar-border px-2 py-2 rounded-md transition-colors">
                                <i data-lucide="settings" class="w-4 h-4"></i>
                                Paramètres
                            </a>
                        @endif
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit"
                                class="w-full flex items-center gap-2 text-left text-sm text-sidebar-foreground hover:text-foreground hover:bg-sidebar-border px-2 py-2 rounded-md transition-colors">
                                <i data-lucide="log-out" class="w-4 h-4"></i>
                                Déconnexion
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </header>

        <!-- Main Panel Content -->
        <main class="flex-1 overflow-y-auto min-h-0 p-3 sm:p-4 bg-background flex flex-col gap-4 sm:mb-4">
            @if (session('success'))
                <div
                    class="mx-auto max-w-4xl p-4 bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800 rounded-xl text-emerald-700 dark:text-emerald-400 text-sm flex items-center gap-2">
                    <i data-lucide="check-circle" class="w-4 h-4 shrink-0"></i>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            @if (session('error'))
                <div
                    class="mx-auto max-w-4xl p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-xl text-red-700 dark:text-red-400 text-sm flex items-center gap-2">
                    <i data-lucide="alert-circle" class="w-4 h-4 shrink-0"></i>
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            @yield('content')
        </main>
    </div>

    @livewireScripts
    @stack('scripts')
</body>

</html>
