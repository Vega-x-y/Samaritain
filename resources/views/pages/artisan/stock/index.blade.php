@extends('layouts.artisan')

@section('title', 'Stock - Artisan')

@section('breadcrumbs')
    <nav class="flex items-center gap-2 text-sm text-muted-foreground">
        <i data-lucide="package" class="w-4 h-4"></i>
        <span>Stock</span>
    </nav>
@endsection

@section('content')
<div class="container mx-auto px-4 py-8" x-data="stockApp()">
    <!-- En-tête -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-8">
        <div>
            <h1 class="text-xl md:text-2xl font-bold text-gray-900 dark:text-white">Mon <span class="text-primary">stock</span></h1>
            <p class="text-gray-500 dark:text-gray-400 mt-1 text-sm">Gérez vos articles et matériaux</p>
        </div>
        <a href="{{ route('artisan.stock.create') }}" class="bg-primary hover:bg-primary/90 text-white px-5 py-2.5 rounded-full font-semibold text-sm transition shadow-md hover:shadow-lg">
            + Nouvel article
        </a>
    </div>

    <!-- Statistiques -->
    <div class="grid grid-cols-2 md:grid-cols-3 gap-3 mb-6">
        <div class="bg-white dark:bg-gray-800 rounded-xl p-3 border border-gray-100 dark:border-gray-700 shadow-sm flex items-center gap-3">
            <div class="w-11 h-11 bg-primary/10 dark:bg-primary/20 rounded-full flex items-center justify-center text-xl"><i data-lucide="package" class="w-4 h-4 inline-block align-middle mr-1"></i></div>
            <div>
                <div class="text-xl font-bold text-gray-900 dark:text-white">{{ $stats['total'] }}</div>
                <div class="text-xs text-gray-500 dark:text-gray-400">Articles</div>
            </div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl p-3 border border-gray-100 dark:border-gray-700 shadow-sm flex items-center gap-3">
            <div class="w-11 h-11 bg-red-50 dark:bg-red-900/30 rounded-full flex items-center justify-center text-xl"><i data-lucide="zap" class="w-4 h-4 inline-block align-middle mr-1"></i>️</div>
            <div>
                <div class="text-xl font-bold text-red-600 dark:text-red-400">{{ $stats['stock_alerte'] }}</div>
                <div class="text-xs text-gray-500 dark:text-gray-400">Alertes stock</div>
            </div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl p-3 border border-gray-100 dark:border-gray-700 shadow-sm flex items-center gap-3">
            <div class="w-11 h-11 bg-emerald-50 dark:bg-emerald-900/30 rounded-full flex items-center justify-center text-xl"><i data-lucide="wallet" class="w-4 h-4 inline-block align-middle mr-1"></i></div>
            <div>
                <div class="text-xl font-bold text-emerald-600 dark:text-emerald-400">{{ number_format($stats['valeur_totale'], 0, ',', ' ') }}</div>
                <div class="text-xs text-gray-500 dark:text-gray-400">Valeur totale (FCFA)</div>
            </div>
        </div>
    </div>

        <!-- Barre de recherche -->
    <div class="mb-4">
        @include('components.artisan.search-bar', ['placeholder' => 'Rechercher un article�?�'])
    </div>

    <!-- Filtres -->
    <div class="flex flex-wrap gap-2 items-center mb-6">
        <a href="{{ route('artisan.stock.index') }}"
            class="px-4 py-1.5 rounded-full text-sm font-medium border transition
                {{ !request('categorie') && !request('search') ? 'bg-gray-900 text-white border-gray-900 dark:bg-white dark:text-gray-900' : 'bg-white dark:bg-gray-800 text-gray-500 dark:text-gray-400 border-gray-200 dark:border-gray-700 hover:border-primary' }}">
            <i data-lucide="chart-no-axes-column" class="w-4 h-4 inline-block align-middle mr-1"></i> Tous
        </a>
        @foreach ($categories as $categorie)
            <a href="{{ route('artisan.stock.index', ['categorie' => $categorie]) }}"
                class="px-4 py-1.5 rounded-full text-sm font-medium border transition
                    {{ request('categorie') === $categorie ? 'bg-gray-900 text-white border-gray-900 dark:bg-white dark:text-gray-900' : 'bg-white dark:bg-gray-800 text-gray-500 dark:text-gray-400 border-gray-200 dark:border-gray-700 hover:border-primary' }}">
                {{ $categorie }}
            </a>
        @endforeach
        <span class="text-xs text-gray-400 dark:text-gray-500 ml-2">{{ $articles->total() }} article(s)</span>
    </div>

    <!-- Grille des articles -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
        @forelse ($articles as $article)
            <div class="bg-white dark:bg-gray-800 rounded-xl p-5 shadow-sm border border-gray-100 dark:border-gray-700 transition hover:-translate-y-1 hover:shadow-md hover:border-primary/40 dark:hover:border-primary">
                <div class="flex justify-between items-start mb-3">
                    <div>
                        <div class="font-semibold text-gray-900 dark:text-white">{{ $article->nom }}</div>
                        @if ($article->reference)
                            <div class="text-xs text-gray-500 dark:text-gray-400">Réf: {{ $article->reference }}</div>
                        @endif
                    </div>
                    @if ($article->stock_alerte)
                        <span class="text-xs font-semibold px-2 py-0.5 rounded-full bg-red-50 text-red-700 dark:bg-red-900/30 dark:text-red-300">
                            <i data-lucide="zap" class="w-4 h-4 inline-block align-middle mr-1"></i>️ Alerte
                        </span>
                    @endif
                </div>

                <div class="space-y-2 text-sm text-gray-600 dark:text-gray-400">
                    @if ($article->categorie)
                        <div class="flex items-center gap-2">
                            <i data-lucide="tag" class="w-4 h-4"></i>
                            <span>{{ $article->categorie }}</span>
                        </div>
                    @endif
                    <div class="flex items-center gap-2">
                        <i data-lucide="box" class="w-4 h-4"></i>
                        <span class="font-medium {{ $article->stock_alerte ? 'text-red-600 dark:text-red-400' : 'text-gray-900 dark:text-white' }}">
                            {{ $article->quantite }} en stock
                        </span>
                    </div>
                    @if ($article->prix_unitaire)
                        <div class="flex items-center gap-2">
                            <i data-lucide="euro" class="w-4 h-4"></i>
                            <span>{{ number_format($article->prix_unitaire, 2, ',', ' ') }} FCFA / unité</span>
                        </div>
                    @endif
                    @if ($article->fournisseur)
                        <div class="flex items-center gap-2">
                            <i data-lucide="truck" class="w-4 h-4"></i>
                            <span class="truncate">{{ $article->fournisseur }}</span>
                        </div>
                    @endif
                </div>

                <div class="flex gap-2 mt-4 pt-3 border-t border-gray-100 dark:border-gray-700">
                    <a href="{{ route('artisan.stock.show', $article) }}" class="flex-1 bg-primary hover:bg-primary/90 text-white text-center py-2 rounded-full text-sm font-medium transition">
                        Voir détails
                    </a>
                    <a href="{{ route('artisan.stock.edit', $article) }}" class="px-3 py-2 bg-blue-500 hover:bg-blue-600 text-white rounded-full text-sm font-medium transition" title="Modifier">
                        <i data-lucide="pencil" class="w-4 h-4 inline-block align-middle mr-1"></i>
                    </a>
                    <form method="POST" action="{{ route('artisan.stock.destroy', $article) }}" class="inline" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cet article ?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="px-3 py-2 bg-red-500 hover:bg-red-600 text-white rounded-full text-sm font-medium transition" title="Supprimer">
                            <i data-lucide="trash-2" class="w-4 h-4 inline-block align-middle mr-1"></i>
                        </button>
                    </form>
                </div>
            </div>
        @empty
            <div class="col-span-full text-center py-16 text-gray-500 dark:text-gray-400">
                <div class="text-5xl mb-2"><i data-lucide="package" class="w-4 h-4 inline-block align-middle mr-1"></i></div>
                <p>Aucun article en stock pour le moment.</p>
                <p class="text-sm mt-1">Ajoutez votre premier article avec le bouton ci-dessus.</p>
            </div>
        @endforelse
    </div>

    <!-- Pagination -->
    <div class="mt-6">
        {{ $articles->links() }}
    </div>

    <!-- Modal Nouvel Article -->
    <div x-show="modalOpen" x-cloak class="fixed inset-0 bg-black/50 backdrop-blur-sm z-[1000] flex items-center justify-center p-4">
        <div class="bg-white dark:bg-gray-800 rounded-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto p-6 shadow-2xl">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-xl font-bold text-gray-900 dark:text-white"><i data-lucide="package" class="w-4 h-4 inline-block align-middle mr-1"></i> Nouvel article</h2>
                <button @click="modalOpen = false" class="text-2xl text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">&times;</button>
            </div>

            <form method="POST" action="{{ route('artisan.stock.store') }}">
                @csrf
                <div class="space-y-4">
                    <div>
                        <label class="block font-semibold text-sm mb-1 text-gray-900 dark:text-white">Nom de l'article *</label>
                        <input type="text" name="nom" value="{{ old('nom') }}" required
                            class="w-full px-4 py-2.5 border border-gray-200 dark:border-gray-600 rounded-lg focus:border-primary focus:ring-2 focus:ring-primary/10 dark:focus:ring-primary/20 outline-none transition bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                        @error('nom') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block font-semibold text-sm mb-1 text-gray-900 dark:text-white">Référence</label>
                            <input type="text" name="reference" value="{{ old('reference') }}"
                                class="w-full px-4 py-2.5 border border-gray-200 dark:border-gray-600 rounded-lg focus:border-primary focus:ring-2 focus:ring-primary/10 dark:focus:ring-primary/20 outline-none transition bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                            @error('reference') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block font-semibold text-sm mb-1 text-gray-900 dark:text-white">Catégorie</label>
                            <input type="text" name="categorie" value="{{ old('categorie') }}" placeholder="Ex: Plomberie, �?lectricité..."
                                class="w-full px-4 py-2.5 border border-gray-200 dark:border-gray-600 rounded-lg focus:border-primary focus:ring-2 focus:ring-primary/10 dark:focus:ring-primary/20 outline-none transition bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                            @error('categorie') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block font-semibold text-sm mb-1 text-gray-900 dark:text-white">Quantité *</label>
                            <input type="number" name="quantite" value="{{ old('quantite') }}" required min="0"
                                class="w-full px-4 py-2.5 border border-gray-200 dark:border-gray-600 rounded-lg focus:border-primary focus:ring-2 focus:ring-primary/10 dark:focus:ring-primary/20 outline-none transition bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                            @error('quantite') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block font-semibold text-sm mb-1 text-gray-900 dark:text-white">Seuil d'alerte *</label>
                            <input type="number" name="seuil_alerte" value="{{ old('seuil_alerte', 5) }}" required min="0"
                                class="w-full px-4 py-2.5 border border-gray-200 dark:border-gray-600 rounded-lg focus:border-primary focus:ring-2 focus:ring-primary/10 dark:focus:ring-primary/20 outline-none transition bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                            @error('seuil_alerte') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block font-semibold text-sm mb-1 text-gray-900 dark:text-white">Prix unitaire (�,�)</label>
                            <input type="number" name="prix_unitaire" value="{{ old('prix_unitaire') }}" min="0" step="0.01"
                                class="w-full px-4 py-2.5 border border-gray-200 dark:border-gray-600 rounded-lg focus:border-primary focus:ring-2 focus:ring-primary/10 dark:focus:ring-primary/20 outline-none transition bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                            @error('prix_unitaire') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block font-semibold text-sm mb-1 text-gray-900 dark:text-white">Fournisseur</label>
                            <input type="text" name="fournisseur" value="{{ old('fournisseur') }}"
                                class="w-full px-4 py-2.5 border border-gray-200 dark:border-gray-600 rounded-lg focus:border-primary focus:ring-2 focus:ring-primary/10 dark:focus:ring-primary/20 outline-none transition bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                            @error('fournisseur') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>

                <div class="mt-6">
                    <button type="submit" class="w-full bg-primary hover:bg-primary/90 text-white py-3 rounded-xl font-semibold transition">Créer l'article</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('stockApp', () => ({
            modalOpen: false,

            openModal() {
                this.modalOpen = true;
            }
        }));
    });
</script>
@endpush
@endsection