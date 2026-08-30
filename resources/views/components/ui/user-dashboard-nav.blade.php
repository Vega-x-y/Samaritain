<section class="border-b border-border">
    <x-blade-components::layout.container>
        <!-- Conteneur avec overflow-x-auto et scrollbar cachée -->
        <div class="overflow-x-auto scrollbar-hide">
            <nav class="flex gap-6 min-w-max">
                @php
                    $messagesNonLus = $messagesNonLus ?? (auth()->user()
                        ? \App\Models\Message::whereHas('conversation', fn ($q) => $q->whereIn('client_id', auth()->user()->clients()->pluck('id')))
                            ->where('lu', false)
                            ->where('expediteur_type', '!=', 'client')
                            ->count()
                        : 0);

                    $tabs = [
                        [
                            'route' => 'owner.dashboard',
                            'icon' => 'layout-dashboard',
                            'label' => 'Portail Propriétaire',
                            'show' => true,
                        ],
                        [
                            'route' => 'property.dashboard',
                            'icon' => 'warehouse',
                            'label' => 'Mes biens',
                            'show' => true,
                        ],
                        [
                            'route' => 'hotel.dashboard',
                            'icon' => 'building-2',
                            'label' => 'Hôtels',
                            'show' => true,
                        ],
                        [
                            'route' => 'parcelles.dashboard',
                            'icon' => 'land-plot',
                            'label' => 'Mes parcelles',
                            'show' => true,
                        ],
                        [
                            'route' => 'artisan.dashboard',
                            'icon' => 'drill',
                            'label' => 'Artisan',
                            'show' => auth()->user()?->artisan,
                        ],
                        [
                            'route' => 'client.dashboard',
                            'icon' => 'users',
                            'label' => 'Client',
                            'show' => true,
                        ],
                        [
                            'route' => 'client.messagerie.index',
                            'icon' => 'message-circle',
                            'label' => 'Messagerie',
                            'show' => auth()->user()?->clients()->exists(),
                            'badge' => $messagesNonLus ?? 0,
                        ],
                        [
                            'route' => 'client.documents.index',
                            'icon' => 'file-text',
                            'label' => 'Documents',
                            'show' => auth()->user()?->clients()->exists(),
                        ],
                        [
                            'route' => 'my-visit-passes.index',
                            'icon' => 'ticket',
                            'label' => 'Pass visite',
                            'show' => true
                        ],
                        [
                            'route' => 'client.transactions',
                            'icon' => 'receipt',
                            'label' => 'Transactions',
                            'show' => true,
                        ],
                        [
                            'route' => 'profile.show',
                            'icon' => 'settings',
                            'label' => 'Paramètres',
                            'show' => true,
                        ],
                    ];
                @endphp

                @foreach ($tabs as $tab)
                    @continue(!$tab['show'])
                    @php $active = $tab['route'] && request()->routeIs($tab['route']); @endphp

                    <a href="{{ $tab['route'] ? route($tab['route']) : $tab['href'] ?? '#' }}" @class([
                        'group flex items-center gap-2 px-1 py-3 text-sm whitespace-nowrap border-b-2 -mb-px transition-colors',
                        'border-primary text-primary font-semibold' => $active,
                        'border-transparent text-muted-foreground hover:text-foreground hover:border-border' => !$active,
                    ])>
                        <i data-lucide="{{ $tab['icon'] }}" @class([
                            'w-4 h-4 transition-colors',
                            'text-primary' => $active,
                            'text-muted-foreground group-hover:text-foreground' => !$active,
                        ])></i>
                        {{ $tab['label'] }}
                        @if (($tab['badge'] ?? 0) > 0)
                            <span class="ml-auto shrink-0 inline-flex items-center justify-center px-1.5 py-0.5 text-[9px] font-bold leading-none text-white bg-red-500 rounded-full min-w-[18px] min-h-[18px]">
                                {{ $tab['badge'] }}
                            </span>
                        @endif
                    </a>
                @endforeach
            </nav>
        </div>
    </x-blade-components::layout.container>
</section>
