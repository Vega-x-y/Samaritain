<aside :class="sidebarOpen ? 'w-64' : 'w-14'"
    class="h-screen bg-[var(--sidebar)] border-r border-[var(--sidebar-border)] flex flex-col shrink-0 overflow-x-hidden transition-all duration-300 ease-in-out select-none">

    <!-- Workspace / Organization Header -->
    <a href="{{ route('index') }}" target="_blank">
        <div class="h-14 border-b border-[var(--sidebar-border)] flex items-center px-3 gap-2 justify-between shrink-0 bg-[var(--sidebar)]">
            <div class="flex items-center gap-2 overflow-hidden w-full">
                <div>
                    <img src="{{ asset('light_logo.svg') }}" alt="light logo" class="w-10 py-3 block dark:hidden">
                    <img src="{{ asset('dark_logo.svg') }}" alt="dark logo" class="w-10 py-3 dark:block hidden">
                </div>

                <div x-show="sidebarOpen" x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                    class="flex flex-col text-left overflow-hidden select-none cursor-pointer flex-1">
                    <span
                        class="text-xs font-semibold text-[var(--sidebar-accent-foreground)] truncate leading-tight">
                        Samaritain
                    </span>
                    <span
                        class="text-[10px] text-[var(--sidebar-accent-foreground)] truncate leading-tight">
                        Artisan
                    </span>
                </div>
            </div>
        </div>
    </a>

    <!-- Navigation Groups -->
    <div class="flex-1 overflow-y-auto">
        <!-- Gestion -->
        <div class="px-2 py-2 flex flex-col gap-0.5">
            <span x-show="sidebarOpen" x-transition:enter="transition ease-out duration-150"
                x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                class="text-[10px] font-medium tracking-wider text-[var(--sidebar-accent-foreground)] uppercase px-2 py-1.5 select-none block">
                Gestion
            </span>

            <ul class="flex flex-col gap-0.5">
                <!-- Dashboard -->
                <li class="relative list-none">
                    <a href="{{ route('artisan.dashboard') }}"
                        class="flex items-center px-3 py-2 rounded-md text-xs font-medium transition-all group select-none
                            {{ request()->routeIs('artisan.dashboard') ? 'bg-[var(--sidebar-primary)] text-[var(--sidebar-primary-foreground)]' : 'text-[var(--sidebar-foreground)] hover:text-[var(--foreground)] hover:bg-[var(--sidebar-border)]' }}">
                        <span class="shrink-0 flex items-center justify-center w-4 h-4">
                            <i data-lucide="layout-dashboard" class="w-4 h-4 transition-colors"></i>
                        </span>
                        <span x-show="sidebarOpen" x-transition:enter="transition ease-out duration-150"
                            x-transition:enter-start="opacity-0 translate-x-1"
                            x-transition:enter-end="opacity-100 translate-x-0"
                            class="ml-2 truncate">Tableau de bord</span>
                    </a>
                </li>

                <!-- Mes clients -->
                <li class="relative list-none">
                    <a href="{{ route('artisan.clients.index') }}"
                        class="flex items-center px-3 py-2 rounded-md text-xs font-medium transition-all group select-none
                            {{ request()->routeIs('artisan.clients.*') ? 'bg-[var(--sidebar-primary)] text-[var(--sidebar-primary-foreground)]' : 'text-[var(--sidebar-foreground)] hover:text-[var(--foreground)] hover:bg-[var(--sidebar-border)]' }}">
                        <span class="shrink-0 flex items-center justify-center w-4 h-4">
                            <i data-lucide="users" class="w-4 h-4 transition-colors"></i>
                        </span>
                        <span x-show="sidebarOpen" x-transition:enter="transition ease-out duration-150"
                            x-transition:enter-start="opacity-0 translate-x-1"
                            x-transition:enter-end="opacity-100 translate-x-0"
                            class="ml-2 truncate">Clients</span>

                        @if (($stats['clients_count'] ?? 0) > 0)
                            <span
                                class="ml-auto shrink-0 inline-flex items-center justify-center px-1.5 py-0.5 text-[9px] font-bold leading-none text-[var(--sidebar-primary-foreground)] bg-[var(--sidebar-primary)] rounded-full min-w-[18px] min-h-[18px]">
                                {{ $stats['clients_count'] }}
                            </span>
                        @endif
                    </a>
                </li>

                <!-- Stock -->
                <li class="relative list-none">
                    <a href="{{ route('artisan.stock.index') }}"
                        class="flex items-center px-3 py-2 rounded-md text-xs font-medium transition-all group select-none
                            {{ request()->routeIs('artisan.stock.*') ? 'bg-[var(--sidebar-primary)] text-[var(--sidebar-primary-foreground)]' : 'text-[var(--sidebar-foreground)] hover:text-[var(--foreground)] hover:bg-[var(--sidebar-border)]' }}">
                        <span class="shrink-0 flex items-center justify-center w-4 h-4">
                            <i data-lucide="package" class="w-4 h-4 transition-colors"></i>
                        </span>
                        <span x-show="sidebarOpen" x-transition:enter="transition ease-out duration-150"
                            x-transition:enter-start="opacity-0 translate-x-1"
                            x-transition:enter-end="opacity-100 translate-x-0"
                            class="ml-2 truncate">Stock</span>

                        @if (($stats['stock_alerte'] ?? 0) > 0)
                            <span
                                class="ml-auto shrink-0 inline-flex items-center justify-center px-1.5 py-0.5 text-[9px] font-bold leading-none text-white bg-red-500 rounded-full min-w-[18px] min-h-[18px]">
                                {{ $stats['stock_alerte'] }}
                            </span>
                        @endif
                    </a>
                </li>

                <!-- Finances -->
                <li class="relative list-none">
                    <a href="{{ route('artisan.finances.index') }}"
                        class="flex items-center px-3 py-2 rounded-md text-xs font-medium transition-all group select-none
                            {{ request()->routeIs('artisan.finances.*') ? 'bg-[var(--sidebar-primary)] text-[var(--sidebar-primary-foreground)]' : 'text-[var(--sidebar-foreground)] hover:text-[var(--foreground)] hover:bg-[var(--sidebar-border)]' }}">
                        <span class="shrink-0 flex items-center justify-center w-4 h-4">
                            <i data-lucide="wallet" class="w-4 h-4 transition-colors"></i>
                        </span>
                        <span x-show="sidebarOpen" x-transition:enter="transition ease-out duration-150"
                            x-transition:enter-start="opacity-0 translate-x-1"
                            x-transition:enter-end="opacity-100 translate-x-0"
                            class="ml-2 truncate">Finances</span>
                    </a>
                </li>

                <!-- Documents -->
                <li class="relative list-none">
                    <a href="{{ route('artisan.documents.index') }}"
                        class="flex items-center px-3 py-2 rounded-md text-xs font-medium transition-all group select-none
                            {{ request()->routeIs('artisan.documents.*') ? 'bg-[var(--sidebar-primary)] text-[var(--sidebar-primary-foreground)]' : 'text-[var(--sidebar-foreground)] hover:text-[var(--foreground)] hover:bg-[var(--sidebar-border)]' }}">
                        <span class="shrink-0 flex items-center justify-center w-4 h-4">
                            <i data-lucide="folder" class="w-4 h-4 transition-colors"></i>
                        </span>
                        <span x-show="sidebarOpen" x-transition:enter="transition ease-out duration-150"
                            x-transition:enter-start="opacity-0 translate-x-1"
                            x-transition:enter-end="opacity-100 translate-x-0"
                            class="ml-2 truncate">Documents</span>
                    </a>
                </li>

                <!-- Planning -->
                <li class="relative list-none">
                    <a href="{{ route('artisan.planning.index') }}"
                        class="flex items-center px-3 py-2 rounded-md text-xs font-medium transition-all group select-none
                            {{ request()->routeIs('artisan.planning.*') ? 'bg-[var(--sidebar-primary)] text-[var(--sidebar-primary-foreground)]' : 'text-[var(--sidebar-foreground)] hover:text-[var(--foreground)] hover:bg-[var(--sidebar-border)]' }}">
                        <span class="shrink-0 flex items-center justify-center w-4 h-4">
                            <i data-lucide="calendar" class="w-4 h-4 transition-colors"></i>
                        </span>
                        <span x-show="sidebarOpen" x-transition:enter="transition ease-out duration-150"
                            x-transition:enter-start="opacity-0 translate-x-1"
                            x-transition:enter-end="opacity-100 translate-x-0"
                            class="ml-2 truncate">Planning</span>
                    </a>
                </li>

                <!-- Messagerie -->
                <li class="relative list-none">
                    <a href="{{ route('artisan.messagerie.index') }}"
                        class="flex items-center px-3 py-2 rounded-md text-xs font-medium transition-all group select-none
                            {{ request()->routeIs('artisan.messagerie.*') ? 'bg-[var(--sidebar-primary)] text-[var(--sidebar-primary-foreground)]' : 'text-[var(--sidebar-foreground)] hover:text-[var(--foreground)] hover:bg-[var(--sidebar-border)]' }}">
                        <span class="shrink-0 flex items-center justify-center w-4 h-4">
                            <i data-lucide="message-circle" class="w-4 h-4 transition-colors"></i>
                        </span>
                        <span x-show="sidebarOpen" x-transition:enter="transition ease-out duration-150"
                            x-transition:enter-start="opacity-0 translate-x-1"
                            x-transition:enter-end="opacity-100 translate-x-0"
                            class="ml-2 truncate">Messagerie</span>

                        @if (($messagesNonLus ?? 0) > 0)
                            <span
                                class="ml-auto shrink-0 inline-flex items-center justify-center px-1.5 py-0.5 text-[9px] font-bold leading-none text-white bg-red-500 rounded-full min-w-[18px] min-h-[18px]">
                                {{ $messagesNonLus }}
                            </span>
                        @endif
                    </a>
                </li>

                <!-- Mes chantiers -->
                <li class="relative list-none">
                    <a href="{{ route('artisan.chantiers.index') }}"
                        class="flex items-center px-3 py-2 rounded-md text-xs font-medium transition-all group select-none
                            {{ request()->routeIs('artisan.chantiers.*') ? 'bg-[var(--sidebar-primary)] text-[var(--sidebar-primary-foreground)]' : 'text-[var(--sidebar-foreground)] hover:text-[var(--foreground)] hover:bg-[var(--sidebar-border)]' }}">
                        <span class="shrink-0 flex items-center justify-center w-4 h-4">
                            <i data-lucide="hard-hat" class="w-4 h-4 transition-colors"></i>
                        </span>
                        <span x-show="sidebarOpen" x-transition:enter="transition ease-out duration-150"
                            x-transition:enter-start="opacity-0 translate-x-1"
                            x-transition:enter-end="opacity-100 translate-x-0"
                            class="ml-2 truncate">Mes chantiers</span>

                        @if (($stats['chantiers_count'] ?? 0) > 0)
                            <span
                                class="ml-auto shrink-0 inline-flex items-center justify-center px-1.5 py-0.5 text-[9px] font-bold leading-none text-[var(--sidebar-primary-foreground)] bg-[var(--sidebar-primary)] rounded-full min-w-[18px] min-h-[18px]">
                                {{ $stats['chantiers_count'] }}
                            </span>
                        @endif
                    </a>
                </li>

                <!-- Mes réalisations -->
                <li class="relative list-none">
                    <a href="{{ route('artisan.projects.index', $artisan ?? 0) }}"
                        class="flex items-center px-3 py-2 rounded-md text-xs font-medium transition-all group select-none
                            {{ request()->routeIs('artisan.projects.*') ? 'bg-[var(--sidebar-primary)] text-[var(--sidebar-primary-foreground)]' : 'text-[var(--sidebar-foreground)] hover:text-[var(--foreground)] hover:bg-[var(--sidebar-border)]' }}">
                        <span class="shrink-0 flex items-center justify-center w-4 h-4">
                            <i data-lucide="images" class="w-4 h-4 transition-colors"></i>
                        </span>
                        <span x-show="sidebarOpen" x-transition:enter="transition ease-out duration-150"
                            x-transition:enter-start="opacity-0 translate-x-1"
                            x-transition:enter-end="opacity-100 translate-x-0"
                            class="ml-2 truncate">Mes réalisations</span>

                        @if (($stats['projects_count'] ?? 0) > 0)
                            <span
                                class="ml-auto shrink-0 inline-flex items-center justify-center px-1.5 py-0.5 text-[9px] font-bold leading-none text-[var(--sidebar-primary-foreground)] bg-[var(--sidebar-primary)] rounded-full min-w-[18px] min-h-[18px]">
                                {{ $stats['projects_count'] }}
                            </span>
                        @endif
                    </a>
                </li>

                <!-- Mon profil -->
                <li class="relative list-none">
                    <a href="{{ route('artisan.profile') }}"
                        class="flex items-center px-3 py-2 rounded-md text-xs font-medium transition-all group select-none
                            {{ request()->routeIs('artisan.profile') ? 'bg-[var(--sidebar-primary)] text-[var(--sidebar-primary-foreground)]' : 'text-[var(--sidebar-foreground)] hover:text-[var(--foreground)] hover:bg-[var(--sidebar-border)]' }}">
                        <span class="shrink-0 flex items-center justify-center w-4 h-4">
                            <i data-lucide="user" class="w-4 h-4 transition-colors"></i>
                        </span>
                        <span x-show="sidebarOpen" x-transition:enter="transition ease-out duration-150"
                            x-transition:enter-start="opacity:0 translate-x-1"
                            x-transition:enter-end="opacity-100 translate-x-0"
                            class="ml-2 truncate">Mon profil</span>
                    </a>
                </li>
            </ul>
        </div>
    </div>

    <!-- User Profile Footer -->
    <div x-data="{ open: false }" x-on:click="open = !open" @keydown.escape="open = false" type="button" aria-haspopup="true"
        :aria-expanded="open.toString()"
        class="h-14 border-t border-[var(--sidebar-border)] flex items-center px-3 gap-2 justify-between shrink-0 bg-[var(--sidebar)]/80 mt-auto cursor-pointer">
        <div class="flex items-center gap-2 overflow-hidden w-full">
            @if (auth()->user()->profileUrl())
                <img src="{{ auth()->user()->profileUrl() }}" alt="{{ auth()->user()->name }}"
                    class="w-7 h-7 rounded-md shrink-0 object-cover border border-gray-200 shadow-sm">
            @else
                <div
                    class="w-7 h-7 rounded-md shrink-0 bg-zinc-700 flex items-center justify-center text-xs font-medium text-white">
                    {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                </div>
            @endif

            <div x-show="sidebarOpen" x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity:100 scale-100"
                class="flex flex-col text-left overflow-hidden flex-1 select-none">
                <span
                    class="text-xs font-semibold text-[var(--sidebar-accent-foreground)] truncate leading-tight">{{ auth()->user()->name }}</span>
                <span
                    class="text-[10px] text-[var(--sidebar-accent-foreground)] truncate leading-tight">{{ auth()->user()->email }}</span>
            </div>
        </div>

        <div x-show="sidebarOpen" class="shrink-0" x-cloak>
            <div class="relative text-[var(--sidebar-accent-foreground)]">
                <button class="p-1 rounded-md hover:bg-white/5">
                    <i data-lucide="chevrons-up-down" height="16" width="16"></i>
                </button>

                <div x-show="open" x-transition:enter="transition ease-out duration-100"
                    x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity:100 translate-y-0"
                    x-transition:leave="transition ease-in duration-75" x-transition:leave-start="opacity:100 translate-y-0"
                    x-transition:leave-end="opacity:0 -translate-y-1" x-on:click.away="open = false"
                    @keydown.escape.window="open = false" x-cloak
                    class="origin-bottom-right absolute right-0 bottom-full mb-2 w-48 bg-[var(--sidebar)] border border-[var(--sidebar-border)] rounded-md shadow-lg z-50 overflow-hidden flex flex-col gap-2 p-1">
                    <a href="{{ route('profile.show') }}"
                        class="flex gap-1 text-sm text-[var(--sidebar-accent-foreground)] hover:text-[var(--foreground)] hover:bg-[var(--sidebar-border)] px-2 py-2 rounded-md">
                        <i data-lucide="user" class="w-4 h-4 mr-2"></i>
                        Profil
                    </a>
                    <a href="{{ route('artisan.edit', $artisan ?? 0) }}"
                        class="flex gap-1 text-sm text-[var(--sidebar-accent-foreground)] hover:text-[var(--foreground)] hover:bg-[var(--sidebar-border)] px-2 py-2 rounded-md">
                        <i data-lucide="settings" class="w-4 h-4 mr-2"></i>
                        Paramètres
                    </a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit"
                            class="w-full flex gap-1 text-left text-sm text-[var(--sidebar-accent-foreground)] hover:text-[var(--foreground)] hover:bg-[var(--sidebar-border)] px-2 py-2 rounded-md">
                            <i data-lucide="log-out" class="w-4 h-4 mr-2"></i>
                            Déconnexion
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</aside>
