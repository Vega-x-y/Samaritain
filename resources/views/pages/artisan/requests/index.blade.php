@extends('layouts.artisan')

@section('title', 'Demandes reçues')

@section('breadcrumbs')
    <nav class="flex items-center gap-2 text-sm text-muted-foreground">
        <i data-lucide="mail" class="w-4 h-4"></i>
        <span>Demandes reçues</span>
    </nav>
@endsection

@section('content')
    <div class="space-y-6">
        <!-- En-tête -->
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-foreground">Demandes reçues</h1>
                <p class="text-sm text-muted-foreground mt-1">Demandes de devis, informations et rendez-vous</p>
            </div>
        </div>

        <!-- Stats -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="bg-card rounded-lg shadow-sm border border-border p-4 transition-all duration-300 hover:shadow-lg hover:-translate-y-1">
                <div class="flex items-center gap-3">
                    <div class="p-3 bg-yellow-100 dark:bg-yellow-900/30 rounded-lg transition-transform duration-300 hover:scale-110">
                        <i data-lucide="clock" class="w-6 h-6 text-yellow-600"></i>
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-foreground">{{ $demandes->where('statut', 'en_attente')->count() }}</p>
                        <p class="text-xs text-muted-foreground">En attente</p>
                    </div>
                </div>
            </div>
            <div class="bg-card rounded-lg shadow-sm border border-border p-4 transition-all duration-300 hover:shadow-lg hover:-translate-y-1">
                <div class="flex items-center gap-3">
                    <div class="p-3 bg-green-100 dark:bg-green-900/30 rounded-lg transition-transform duration-300 hover:scale-110">
                        <i data-lucide="check-circle" class="w-6 h-6 text-green-600"></i>
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-foreground">{{ $demandes->where('statut', 'acceptee')->count() }}</p>
                        <p class="text-xs text-muted-foreground">Acceptées</p>
                    </div>
                </div>
            </div>
            <div class="bg-card rounded-lg shadow-sm border border-border p-4 transition-all duration-300 hover:shadow-lg hover:-translate-y-1">
                <div class="flex items-center gap-3">
                    <div class="p-3 bg-red-100 dark:bg-red-900/30 rounded-lg transition-transform duration-300 hover:scale-110">
                        <i data-lucide="x-circle" class="w-6 h-6 text-red-600"></i>
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-foreground">{{ $demandes->where('statut', 'refusee')->count() }}</p>
                        <p class="text-xs text-muted-foreground">Refusées</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Barre de recherche -->
        <div class="mb-4">
            @include('components.artisan.search-bar', ['placeholder' => 'Rechercher une demande�?�'])
        </div>

        <!-- Liste des demandes -->
        <!-- Liste des demandes -->
        <div class="bg-card rounded-lg shadow-sm border border-border transition-all duration-300 hover:shadow-lg">
            @if($demandes->count() > 0)
                <div class="divide-y divide-border">
                    @foreach($demandes as $demande)
                        <div class="p-6 transition-colors duration-150 hover:bg-muted/30">
                            <div class="flex items-start gap-4">
                                <div class="shrink-0">
                                    @if($demande->user->profile_image)
                                        <img src="{{ asset('storage/' . $demande->user->profile_image) }}" 
                                             alt="{{ $demande->user->name }}" 
                                             class="w-10 h-10 rounded-full object-cover border border-border transition-transform duration-300 hover:scale-110">
                                    @else
                                        <div class="w-10 h-10 rounded-full bg-blue-500 flex items-center justify-center text-white text-sm font-bold transition-transform duration-300 hover:scale-110">
                                            {{ strtoupper(substr($demande->user->name, 0, 2)) }}
                                        </div>
                                    @endif
                                </div>
                                <div class="flex-1">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-2">
                                            <p class="text-sm font-semibold text-foreground">{{ $demande->user->name }}</p>
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium transition-colors duration-200
                                                {{ $demande->statut === 'en_attente' ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400' : 
                                                   ($demande->statut === 'acceptee' ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400' : 
                                                   'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400') }}">
                                                {{ $demande->statut === 'en_attente' ? 'En attente' : ($demande->statut === 'acceptee' ? 'Acceptée' : 'Refusée') }}
                                            </span>
                                        </div>
                                        <span class="text-xs text-muted-foreground">{{ $demande->created_at->diffForHumans() }}</span>
                                    </div>
                                    <div class="mt-2">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400 mb-2">
                                            {{ ucfirst($demande->type) }}
                                        </span>
                                        <p class="text-sm text-foreground">{{ $demande->message }}</p>
                                    </div>
                                    
                                    @if($demande->reponse)
                                        <div class="mt-3 bg-muted/50 rounded-lg p-3 border border-border transition-all duration-300 hover:bg-muted/70">
                                            <p class="text-xs font-medium text-muted-foreground mb-1">Votre réponse :</p>
                                            <p class="text-sm text-foreground">{{ $demande->reponse }}</p>
                                            <p class="text-xs text-muted-foreground mt-1">{{ $demande->date_reponse->diffForHumans() }}</p>
                                        </div>
                                    @endif

                                    @if($demande->statut === 'en_attente')
                                        <div class="mt-3 flex gap-2">
                                            <form action="{{ route('artisan.requests.update', $demande) }}" method="POST" class="flex-1 flex gap-2">
                                                @csrf
                                                @method('PATCH')
                                                <input type="hidden" name="statut" value="acceptee">
                                                <div class="flex-1 flex flex-col gap-2">
                                                    <textarea name="reponse" rows="2" required 
                                                        placeholder="Votre réponse..."
                                                        class="w-full rounded-lg border-border focus:border-primary focus:ring-primary text-sm bg-background transition-all duration-200 focus:shadow-md"></textarea>
                                                    <button type="submit" class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg transition-all duration-200 hover:scale-105 active:scale-95 text-sm">
                                                        Accepter
                                                    </button>
                                                </div>
                                            </form>
                                            <form action="{{ route('artisan.requests.update', $demande) }}" method="POST" class="flex flex-col gap-2">
                                                @csrf
                                                @method('PATCH')
                                                <input type="hidden" name="statut" value="refusee">
                                                <textarea name="reponse" rows="2" required 
                                                    placeholder="Motif du refus..."
                                                    class="w-full rounded-lg border-border focus:border-primary focus:ring-primary text-sm bg-background transition-all duration-200 focus:shadow-md"></textarea>
                                                <button type="submit" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg transition-all duration-200 hover:scale-105 active:scale-95 text-sm">
                                                    Refuser
                                                </button>
                                            </form>
                                        </div>
                                    @endif

                                    <div class="mt-2">
                                        <form action="{{ route('artisan.requests.destroy', $demande) }}" method="POST" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-xs text-muted-foreground hover:text-red-500 transition-colors duration-200" 
                                                onclick="return confirm('Supprimer cette demande ?')">
                                                Supprimer
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
                <div class="px-6 py-4 border-t border-border">
                    {{ $demandes->links() }}
                </div>
            @else
                <div class="p-12 text-center text-muted-foreground">
                    <i data-lucide="mail" class="w-12 h-12 mx-auto mb-4 text-muted-foreground/60"></i>
                    <p>Aucune demande reçue pour le moment</p>
                </div>
            @endif
        </div>
    </div>
@endsection