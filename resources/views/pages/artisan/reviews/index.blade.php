@extends('layouts.artisan')

@section('title', 'Avis reçus')

@section('breadcrumbs')
    <nav class="flex items-center gap-2 text-sm text-muted-foreground">
        <i data-lucide="star" class="w-4 h-4"></i>
        <span>Avis reçus</span>
    </nav>
@endsection

@section('content')
    <div class="space-y-6">
        <!-- En-tête -->
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-foreground">Avis reçus</h1>
                <p class="text-sm text-muted-foreground mt-1">Tous les avis laissés par vos clients</p>
            </div>
        </div>

        <!-- Stats -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="bg-card rounded-lg shadow-sm border border-border p-4 transition-all duration-300 hover:shadow-lg hover:-translate-y-1">
                <div class="flex items-center gap-3">
                    <div class="p-3 bg-orange-100 dark:bg-orange-900/30 rounded-lg transition-transform duration-300 hover:scale-110">
                        <i data-lucide="star" class="w-6 h-6 text-orange-500"></i>
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-foreground">{{ $artisan->average_rating }}/5</p>
                        <p class="text-xs text-muted-foreground">Note moyenne</p>
                    </div>
                </div>
            </div>
            <div class="bg-card rounded-lg shadow-sm border border-border p-4 transition-all duration-300 hover:shadow-lg hover:-translate-y-1">
                <div class="flex items-center gap-3">
                    <div class="p-3 bg-orange-100 dark:bg-orange-900/30 rounded-lg transition-transform duration-300 hover:scale-110">
                        <i data-lucide="message-circle" class="w-6 h-6 text-orange-500"></i>
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-foreground">{{ $avis->total() }}</p>
                        <p class="text-xs text-muted-foreground">Total avis</p>
                    </div>
                </div>
            </div>
            <div class="bg-card rounded-lg shadow-sm border border-border p-4 transition-all duration-300 hover:shadow-lg hover:-translate-y-1">
                <div class="flex items-center gap-3">
                    <div class="p-3 bg-orange-100 dark:bg-orange-900/30 rounded-lg transition-transform duration-300 hover:scale-110">
                        <i data-lucide="thumbs-up" class="w-6 h-6 text-orange-500"></i>
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-foreground">
                            {{ $avis->where('rating', '>=', 4)->count() }}
                        </p>
                        <p class="text-xs text-muted-foreground">Avis positifs</p>
                    </div>
                </div>
            </div>
        </div>
        <!-- Barre de recherche -->
        <div class="mb-4">
            @include('components.artisan.search-bar', ['placeholder' => 'Rechercher un avis…'])
        </div>

        <!-- Liste des avis -->

        <!-- Liste des avis -->
        <div class="bg-card rounded-lg shadow-sm border border-border transition-all duration-300 hover:shadow-lg">
            @if($avis->count() > 0)
                <div class="divide-y divide-border">
                    @foreach($avis as $avisItem)
                        <div class="p-6 transition-colors duration-150 hover:bg-muted/30">
                            <div class="flex items-start gap-4">
                                <div class="shrink-0">
                                    @if($avisItem->user->profile_image)
                                        <img src="{{ asset('storage/' . $avisItem->user->profile_image) }}" 
                                             alt="{{ $avisItem->user->name }}" 
                                             class="w-10 h-10 rounded-full object-cover border border-border transition-transform duration-300 hover:scale-110">
                                    @else
                                        <div class="w-10 h-10 rounded-full bg-orange-500 flex items-center justify-center text-white text-sm font-bold transition-transform duration-300 hover:scale-110">
                                            {{ strtoupper(substr($avisItem->user->name, 0, 2)) }}
                                        </div>
                                    @endif
                                </div>
                                <div class="flex-1">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-2">
                                            <p class="text-sm font-semibold text-foreground">{{ $avisItem->user->name }}</p>
                                            <div class="flex items-center gap-0.5">
                                                @for($i = 1; $i <= 5; $i++)
                                                    <i data-lucide="star" 
                                                       class="w-4 h-4 {{ $i <= $avisItem->rating ? 'text-yellow-400 fill-yellow-400' : 'text-gray-300' }} transition-colors duration-200"></i>
                                                @endfor
                                            </div>
                                        </div>
                                        <span class="text-xs text-muted-foreground">{{ $avisItem->created_at->diffForHumans() }}</span>
                                    </div>
                                    @if($avisItem->comment)
                                        <p class="text-sm text-foreground mt-2">{{ $avisItem->comment }}</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
                <div class="px-6 py-4 border-t border-border">
                    {{ $avis->links() }}
                </div>
            @else
                <div class="p-12 text-center text-muted-foreground">
                    <i data-lucide="star" class="w-12 h-12 mx-auto mb-4 text-muted-foreground/60"></i>
                    <p>Aucun avis reçu pour le moment</p>
                </div>
            @endif
        </div>
    </div>
@endsection