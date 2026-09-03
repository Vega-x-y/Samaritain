@extends('layouts.artisan')

@section('content')
    <div class="space-y-6">
        <div class="flex items-center gap-4">
            @if($artisan->avatar)
                <img src="{{ asset('storage/' . $artisan->avatar) }}"
                     alt="{{ $artisan->business_name }}"
                     class="w-14 h-14 rounded-full object-cover border border-border">
            @else
                <div class="w-14 h-14 rounded-full bg-orange-500 flex items-center justify-center text-white text-lg font-bold">
                    {{ strtoupper(substr($artisan->business_name, 0, 2)) }}
                </div>
            @endif
            <div>
                <h1 class="text-2xl font-bold text-foreground">Dashboard</h1>
                <p class="text-sm text-muted-foreground mt-1">Bienvenue, {{ $artisan->business_name }}</p>
            </div>
        </div>
        <!-- Stat Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <x-stat-card title="Chantiers en cours" :value="$stats['projets_en_cours']" color="blue">
                <x-slot name="icon">
                    <i data-lucide="clipboard-list" class="w-8 h-8"></i>
                </x-slot>
            </x-stat-card>

            <x-stat-card title="Client" :value="$stats['clients_actifs']" color="green">
                <x-slot name="icon">
                    <i data-lucide="users" class="w-8 h-8"></i>
                </x-slot>
            </x-stat-card>

            <x-stat-card title="CA Total" :value="$stats['ca_total']" color="green" format="currency">
                <x-slot name="icon">
                    <i data-lucide="banknote" class="w-8 h-8"></i>
                </x-slot>
            </x-stat-card>

            <x-stat-card title="Satisfaction" :value="$stats['satisfaction']" color="yellow" format="decimal">
                <x-slot name="icon">
                    <i data-lucide="star" class="w-8 h-8"></i>
                </x-slot>
            </x-stat-card>

            <x-stat-card title="Stock critique" :value="$stats['stock_critique']" color="red">
                <x-slot name="icon">
                    <i data-lucide="triangle-alert" class="w-8 h-8"></i>
                </x-slot>
            </x-stat-card>

            <x-stat-card title="Messages non lus" :value="$stats['messages_non_lus']" color="purple">
                <x-slot name="icon">
                    <i data-lucide="message-circle" class="w-8 h-8"></i>
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
                    <i data-lucide="chart-no-axes-column" class="mx-auto h-12 w-12 text-muted-foreground/60 mb-4"></i>
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
                                            <i data-lucide="star" class="w-4 h-4 {{ $i <= $review->rating ? 'text-yellow-400' : 'text-muted-foreground/40' }}"></i>
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