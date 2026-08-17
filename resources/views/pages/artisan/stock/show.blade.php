@extends('layouts.artisan')

@section('title', $article->nom.' - Stock - Artisan')

@section('breadcrumbs')
    <nav class="flex items-center gap-2 text-sm text-muted-foreground">
        <a href="{{ route('artisan.stock.index') }}" class="hover:text-foreground transition-colors flex items-center gap-1">
            <i data-lucide="package" class="w-4 h-4"></i>
            <span>Stock</span>
        </a>
        <span class="text-muted-foreground">/</span>
        <span class="text-foreground font-medium truncate max-w-48">{{ $article->nom }}</span>
    </nav>
@endsection

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- En-tête -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-8">
        <div class="flex items-center gap-4">
            <div class="w-16 h-16 rounded-full bg-orange-100 dark:bg-orange-900/30 flex items-center justify-center text-2xl font-bold text-orange-600 dark:text-orange-400">
                📦
            </div>
            <div>
                <h1 class="text-xl md:text-2xl font-bold text-gray-900 dark:text-white">{{ $article->nom }}</h1>
                <p class="text-gray-500 dark:text-gray-400 mt-1 text-sm">
                    @if ($article->reference)
                        Réf: {{ $article->reference }}
                    @else
                        Aucune référence
                    @endif
                </p>
            </div>
        </div>
        <div class="flex gap-2">
            <form method="POST" action="{{ route('artisan.stock.destroy', $article) }}" onsubmit="return confirm('Supprimer cet article ?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="px-4 py-2 rounded-full text-sm font-medium border border-red-300 text-red-600 hover:bg-red-50 dark:border-red-800 dark:text-red-400 dark:hover:bg-red-900/20 transition">
                    Supprimer
                </button>
            </form>
            <a href="{{ route('artisan.stock.index') }}" class="px-4 py-2 rounded-full text-sm font-medium border border-gray-300 text-gray-600 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-400 dark:hover:bg-gray-800 transition">
                ← Retour
            </a>
        </div>
    </div>

    <!-- Grille d'information -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Informations générales -->
        <div class="bg-sidebar dark:bg-gray-800 rounded-xl p-5 border border-accent dark:border-gray-700">
            <h3 class="text-xs uppercase font-semibold text-gray-400 dark:text-gray-500 mb-3 border-b border-gray-100 dark:border-gray-700 pb-2">Informations</h3>
            <div class="space-y-2 text-sm">
                @if ($article->categorie)
                    <div class="flex justify-between">
                        <span class="text-gray-500 dark:text-gray-400">Catégorie</span>
                        <span class="font-medium text-gray-900 dark:text-white">{{ $article->categorie }}</span>
                    </div>
                @endif
                <div class="flex justify-between">
                    <span class="text-gray-500 dark:text-gray-400">Quantité</span>
                    <span class="font-bold text-lg {{ $article->stock_alerte ? 'text-red-600 dark:text-red-400' : 'text-gray-900 dark:text-white' }}">
                        {{ $article->quantite }}
                    </span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500 dark:text-gray-400">Seuil d'alerte</span>
                    <span class="font-medium text-gray-900 dark:text-white">{{ $article->seuil_alerte }}</span>
                </div>
                @if ($article->prix_unitaire)
                    <div class="flex justify-between">
                        <span class="text-gray-500 dark:text-gray-400">Prix unitaire</span>
                        <span class="font-medium text-gray-900 dark:text-white">{{ number_format($article->prix_unitaire, 2, ',', ' ') }} €</span>
                    </div>
                @endif
                @if ($article->fournisseur)
                    <div class="flex justify-between">
                        <span class="text-gray-500 dark:text-gray-400">Fournisseur</span>
                        <span class="font-medium text-gray-900 dark:text-white">{{ $article->fournisseur }}</span>
                    </div>
                @endif
                <div class="flex justify-between">
                    <span class="text-gray-500 dark:text-gray-400">Valeur totale</span>
                    <span class="font-bold text-emerald-600 dark:text-emerald-400">{{ number_format($article->valeur_totale, 2, ',', ' ') }} €</span>
                </div>
            </div>
        </div>

        <!-- Formulaire mouvement -->
        <div class="bg-sidebar dark:bg-gray-800 rounded-xl p-5 border border-accent dark:border-gray-700">
            <h3 class="text-xs uppercase font-semibold text-gray-400 dark:text-gray-500 mb-3 border-b border-gray-100 dark:border-gray-700 pb-2">📝 Enregistrer un mouvement</h3>
            <form method="POST" action="{{ route('artisan.stock.mouvement', $article) }}" class="space-y-4">
                @csrf
                <div>
                    <label class="block font-semibold text-sm mb-1 text-gray-900 dark:text-white">Type *</label>
                    <select name="type" required
                        class="w-full px-4 py-2.5 border border-gray-200 dark:border-gray-600 rounded-lg focus:border-orange-500 focus:ring-2 focus:ring-orange-100 dark:focus:ring-orange-900 outline-none transition bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                        <option value="entree">📥 Entrée</option>
                        <option value="sortie">📤 Sortie</option>
                    </select>
                </div>

                <div>
                    <label class="block font-semibold text-sm mb-1 text-gray-900 dark:text-white">Quantité *</label>
                    <input type="number" name="quantite" required min="1" max="{{ $article->quantite }}"
                        class="w-full px-4 py-2.5 border border-gray-200 dark:border-gray-600 rounded-lg focus:border-orange-500 focus:ring-2 focus:ring-orange-100 dark:focus:ring-orange-900 outline-none transition bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                    @error('quantite') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block font-semibold text-sm mb-1 text-gray-900 dark:text-white">Motif</label>
                    <textarea name="motif" rows="2"
                        class="w-full px-4 py-2.5 border border-gray-200 dark:border-gray-600 rounded-lg focus:border-orange-500 focus:ring-2 focus:ring-orange-100 dark:focus:ring-orange-900 outline-none transition bg-white dark:bg-gray-700 text-gray-900 dark:text-white"></textarea>
                </div>

                <button type="submit" class="w-full bg-orange-500 hover:bg-orange-600 text-white py-2.5 rounded-xl font-semibold transition">
                    Enregistrer le mouvement
                </button>
            </form>
        </div>

        <!-- Historique des mouvements -->
        <div class="bg-sidebar dark:bg-gray-800 rounded-xl p-5 border border-accent dark:border-gray-700 md:col-span-2">
            <h3 class="text-xs uppercase font-semibold text-gray-400 dark:text-gray-500 mb-3 border-b border-gray-100 dark:border-gray-700 pb-2">📋 Historique des mouvements</h3>
            @php $mouvements = $article->mouvements()->latest()->get(); @endphp
            @if ($mouvements->count() > 0)
                <div class="space-y-2">
                    @foreach ($mouvements as $mouvement)
                        <div class="flex items-center justify-between p-3 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                            <div class="flex items-center gap-3">
                                <span class="text-2xl">{{ $mouvement->type->icon() }}</span>
                                <div>
                                    <div class="font-medium text-gray-900 dark:text-white">
                                        {{ $mouvement->type->label() }} de {{ $mouvement->quantite }}
                                    </div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">
                                        {{ $mouvement->date->format('d/m/Y H:i') }}
                                        @if ($mouvement->motif)
                                            · {{ $mouvement->motif }}
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <span class="text-xs font-semibold px-2 py-0.5 rounded-full {{ $mouvement->type->colorClass() }}">
                                {{ $mouvement->type->label() }}
                            </span>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-sm text-gray-500 dark:text-gray-400">Aucun mouvement enregistré.</p>
            @endif
        </div>
    </div>
</div>
@endsection