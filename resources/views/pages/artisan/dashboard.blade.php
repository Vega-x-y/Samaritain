@extends('layouts.artisan')

@section('content')
    <div class="space-y-6">
        <div>
            <h1 class="text-2xl font-bold text-foreground">Dashboard</h1>
            <p class="text-sm text-muted-foreground mt-1">Bienvenue, {{ $artisan->business_name }}</p>
        </div>
        <!-- Stat Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <x-stat-card title="Chantiers en cours" :value="$stats['projets_en_cours']" color="blue">
                <x-slot name="icon">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                    </svg>
                </x-slot>
            </x-stat-card>

            <x-stat-card title="Client" :value="$stats['clients_actifs']" color="green">
                <x-slot name="icon">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                </x-slot>
            </x-stat-card>

            <x-stat-card title="CA Total" :value="$stats['ca_total']" color="green" format="currency">
                <x-slot name="icon">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </x-slot>
            </x-stat-card>

            <x-stat-card title="Satisfaction" :value="$stats['satisfaction']" color="yellow" format="decimal">
                <x-slot name="icon">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                    </svg>
                </x-slot>
            </x-stat-card>

            <x-stat-card title="Stock critique" :value="$stats['stock_critique']" color="red">
                <x-slot name="icon">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </x-slot>
            </x-stat-card>

            <x-stat-card title="Messages non lus" :value="$stats['messages_non_lus']" color="purple">
                <x-slot name="icon">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
                    </svg>
                </x-slot>
            </x-stat-card>
        </div>

        <!-- Graphique CA sur 6 mois -->
        <div class="bg-card rounded-lg shadow-sm border border-border p-6">
            <h3 class="text-lg font-semibold text-foreground mb-6">Chiffre d'affaires sur 6 mois</h3>

            @if(max($ca6Mois) > 0)
                <x-ca-chart :data="$ca6Mois" :labels="$labels6Mois" />
            @else
                <div class="text-center py-8 text-muted-foreground">
                    <svg class="mx-auto h-12 w-12 text-muted-foreground/60 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                    <p>Aucune donnée de CA disponible</p>
                </div>
            @endif
        </div>

        <!-- Derniers avis et contacts -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Derniers avis -->
            <div class="bg-card rounded-lg shadow-sm border border-border p-6">
                <h3 class="text-lg font-semibold text-foreground mb-4">Derniers avis</h3>
                <div class="space-y-4">
                    @forelse($recentReviews as $review)
                        <div class="flex items-start gap-3">
                            <img src="{{ $review->user->profile_image ?? '/default-avatar.png' }}"
                                 alt="{{ $review->user->name }}"
                                 class="w-10 h-10 rounded-full object-cover">
                            <div class="flex-1">
                                <div class="flex items-center justify-between">
                                    <p class="text-sm font-medium text-foreground">{{ $review->user->name }}</p>
                                    <div class="flex items-center">
                                        @for($i = 1; $i <= 5; $i++)
                                            <svg class="w-4 h-4 {{ $i <= $review->rating ? 'text-yellow-400' : 'text-muted-foreground/40' }}" fill="currentColor" viewBox="0 0 20 20">
                                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                                            </svg>
                                        @endfor
                                    </div>
                                </div>
                                <p class="text-sm text-muted-foreground mt-1">{{ $review->comment }}</p>
                                <p class="text-xs text-muted-foreground/60 mt-1">{{ $review->created_at->diffForHumans() }}</p>
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-muted-foreground text-center py-4">Aucun avis pour le moment</p>
                    @endforelse
                </div>
            </div>

            <!-- Derniers contacts -->
            <div class="bg-card rounded-lg shadow-sm border border-border p-6">
                <h3 class="text-lg font-semibold text-foreground mb-4">Derniers contacts</h3>
                <div class="space-y-4">
                    @forelse($recentContacts as $contact)
                        <div class="border-l-4 border-blue-500 pl-4">
                            <p class="text-sm font-medium text-foreground">{{ $contact->name }}</p>
                            <p class="text-sm text-muted-foreground">{{ $contact->email }}</p>
                            <p class="text-sm text-muted-foreground mt-1">{{ Str::limit($contact->message, 100) }}</p>
                            <p class="text-xs text-muted-foreground/60 mt-1">{{ $contact->created_at->diffForHumans() }}</p>
                        </div>
                    @empty
                        <p class="text-sm text-muted-foreground text-center py-4">Aucun contact pour le moment</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
@endsection