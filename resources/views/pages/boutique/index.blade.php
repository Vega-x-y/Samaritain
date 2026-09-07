@extends('layouts.base')

@section('title', 'Tous les boutiques')
@section('title', 'Découvrez nos Boutiques')

@section('content')
    @php
        $routePrefix = 'boutique';
        $propertyLabel = 'Boutiques';
    @endphp
    <!-- Hero Header avec image de fond -->
    <div class="relative bg-gradient-to-r from-black via-black to-black dark:from-blue-800 dark:via-blue-900 dark:to-blue-950 text-white min-h-[90vh] md:min-h-[80vh] lg:min-h-[90vh] flex items-center">
    <!-- Hero Header -->
    <div class="relative bg-gradient-to-r from-black via-black to-black dark:from-blue-800 dark:via-blue-900 dark:to-blue-950 text-white min-h-[50vh] flex items-center">
        <div class="absolute inset-0 overflow-hidden">
            <img src="https://images.unsplash.com/photo-1556740738-b6a63e27c4df?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=2073&q=80"
                alt="Background" class="w-full h-full object-cover opacity-20 dark:opacity-30">
            <img src="https://images.unsplash.com/photo-1556740738-b6a63e27c4df?auto=format&fit=crop&w=2073&q=80"
                alt="Background" class="w-full h-full object-cover opacity-25">
        </div>

        <div class="relative container mx-auto px-4 sm:px-6 lg:px-5 py-5 md:py-5 lg:py-20 text-center">
            <h1 class="text-2xl sm:text-4xl md:text-3xl lg:text-5xl xl:text-6xl font-bold mb-4 md:mb-6 px-2">
        <div class="relative container mx-auto px-4 sm:px-6 lg:px-8 py-16 text-center">
            <h1 class="text-3xl sm:text-4xl md:text-5xl font-bold mb-4">
                Découvrez nos <span class="text-primary dark:text-primary-400">boutiques</span>
            </h1>
            <p class="text-base sm:text-lg md:text-xl lg:text-2xl text-blue-100 dark:text-blue-200 mb-6 md:mb-10 max-w-3xl mx-auto px-4">
                Les meilleures boutiques pour vos besoins commerciaux
            <p class="text-lg md:text-xl text-blue-100 max-w-2xl mx-auto">
                Trouvez l'emplacement commercial idéal pour développer votre activité
            </p>
        </div>

        <div class="absolute bottom-0 left-0 right-0">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 320" class="w-full">
                <path fill="#ffffff" fill-opacity="1" class="dark:fill-gray-900"
                    d="M0,96L48,112C96,128,192,160,288,160C384,160,480,128,576,122.7C672,117,768,139,864,154.7C960,171,1056,181,1152,165.3C1248,149,1344,107,1392,85.3L1440,64L1440,320L1392,320C1344,320,1248,320,1152,320C1056,320,960,320,864,320C768,320,672,320,576,320C480,320,384,320,288,320C192,320,96,320,48,320L0,320Z">
                </path>
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 120" class="w-full h-12 text-background dark:text-gray-950 fill-current">
                <path d="M0,32L120,42.7C240,53,480,75,720,74.7C960,75,1200,53,1320,42.7L1440,32L1440,120L1320,120C1200,120,960,120,720,120C480,120,240,120,120,120L0,120Z"></path>
            </svg>
        </div>
    </div>

    <div class="container mx-auto px-4 sm:px-6 lg:px-8 py-6 md:py-8">
        <!-- Barre de recherche -->
        <div class="bg-card dark:bg-gray-800 rounded-lg shadow-lg -mt-16 md:-mt-20 lg:-mt-24 relative z-10 p-4 sm:p-6 mb-8 md:mb-12">
            <form action="{{ route($routePrefix . '.index') }}" method="GET" id="searchForm">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    <!-- Recherche par mot-clé -->
    <div class="container mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Search & Filter Form -->
        <div class="bg-card dark:bg-gray-800 rounded-xl shadow-lg -mt-16 relative z-10 p-6 mb-12 border border-gray-100 dark:border-gray-700">
            <form action="{{ route('boutique.index') }}" method="GET" id="searchForm">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    <!-- Keyword -->
                    <div>
                        <label class="block text-sm font-medium text-card-foreground dark:text-gray-300 mb-2">Rechercher</label>
                        <div class="relative">
                            <input type="text" name="keyword" id="keyword"
                                placeholder="Nom, localisation..." value="{{ request('keyword') }}"
                                class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-primary dark:focus:ring-primary-400">
                            <i data-lucide="search" class="absolute right-3 top-3 w-4 h-4 text-gray-400"></i>
                        </div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-400 mb-2">Rechercher</label>
                        <input type="text" name="keyword" value="{{ request('keyword') }}" placeholder="Nom, localisation..."
                            class="w-full px-4 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary text-sm">
                    </div>

                    <!-- Tri -->
                    <!-- City -->
                    <div>
                        <label class="block text-sm font-medium text-card-foreground dark:text-gray-300 mb-2">Trier par</label>
                        <select name="sort" id="sort"
                            class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary dark:focus:ring-primary-400">
                            <option value="recent" {{ request('sort') === 'recent' ? 'selected' : '' }}>Plus récentes</option>
                            <option value="name_asc" {{ request('sort') === 'name_asc' ? 'selected' : '' }}>Nom (A-Z)</option>
                            <option value="name_desc" {{ request('sort') === 'name_desc' ? 'selected' : '' }}>Nom (Z-A)</option>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-400 mb-2">Ville</label>
                        <select name="city_id" class="w-full px-4 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary text-sm">
                            <option value="">Toutes les villes</option>
                            @foreach($cities as $city)
                                <option value="{{ $city->id }}" {{ request('city_id') == $city->id ? 'selected' : '' }}>{{ $city->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="flex items-end gap-2">
                        <button type="submit" class="flex-1 px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary-600 dark:hover:bg-primary-700 font-medium transition">
                            <i data-lucide="search" class="inline w-4 h-4 mr-2"></i> Chercher
                        </button>
                    <!-- Min Price -->
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-400 mb-2">Prix max (FC)</label>
                        <input type="number" name="max_price" value="{{ request('max_price') }}" placeholder="Prix max..."
                            class="w-full px-4 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary text-sm">
                    </div>

                    <!-- Sort & Submit -->
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-400 mb-2">Trier par</label>
                        <div class="flex gap-2">
                            <select name="sort" class="w-full px-4 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary text-sm">
                                <option value="latest" {{ request('sort') == 'latest' ? 'selected' : '' }}>Plus récentes</option>
                                <option value="price_asc" {{ request('sort') == 'price_asc' ? 'selected' : '' }}>Prix croissant</option>
                                <option value="price_desc" {{ request('sort') == 'price_desc' ? 'selected' : '' }}>Prix décroissant</option>
                                <option value="name_asc" {{ request('sort') == 'name_asc' ? 'selected' : '' }}>Nom (A-Z)</option>
                            </select>
                            <button type="submit" class="px-5 py-2.5 bg-primary text-white font-medium text-sm rounded-lg hover:bg-primary-600 transition flex items-center gap-1.5 shrink-0">
                                <i data-lucide="search" class="w-4 h-4"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <!-- Affichage des boutiques -->
        <!-- Boutiques Grid -->
        @if(!$boutiques->isEmpty())
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                @foreach ($boutiques as $boutique)
                    <div class="bg-card dark:bg-gray-800 rounded-xl shadow-sm hover:shadow-lg transition overflow-hidden border border-gray-100 dark:border-gray-700">
                        <div class="p-6">
                            <div class="flex items-start justify-between mb-3">
                                <div>
                                    <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-1">{{ $boutique->name }}</h3>
                                    <div class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400">
                                        <i data-lucide="map-pin" class="w-4 h-4"></i>
                                        <span>{{ $boutique->location ?? 'Localisation non spécifiée' }}</span>
                                    </div>
                                </div>
                                @if($boutique->active)
                                    <span class="px-2 py-1 text-xs font-medium text-green-600 dark:text-green-400 bg-green-100 dark:bg-green-900/30 rounded-full">
                                        Actif
                    <div class="group bg-card dark:bg-gray-800 rounded-2xl shadow-sm hover:shadow-xl transition-all duration-300 overflow-hidden border border-gray-100 dark:border-gray-700 flex flex-col">
                        <!-- Image Container -->
                        <div class="relative h-56 overflow-hidden bg-gray-100 dark:bg-gray-900">
                            <img src="{{ $boutique->cover_image_url }}" alt="{{ $boutique->name }}"
                                class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                            <div class="absolute top-3 left-3 flex gap-2">
                                @if($boutique->city)
                                    <span class="px-3 py-1 bg-black/60 backdrop-blur-md text-white text-xs font-semibold rounded-full">
                                        {{ $boutique->city->name }}
                                    </span>
                                @endif
                            </div>

                            @if($boutique->description)
                                <p class="text-sm text-gray-600 dark:text-gray-400 mb-4 line-clamp-2">{{ $boutique->description }}</p>
                            @if($boutique->price)
                                <div class="absolute bottom-3 right-3 px-3 py-1.5 bg-primary text-white text-sm font-bold rounded-lg shadow-md">
                                    {{ number_format($boutique->price, 0, ',', ' ') }} FC / mois
                                </div>
                            @endif
                        </div>

                            <div class="space-y-2 mb-4 text-sm text-gray-600 dark:text-gray-400">
                                @if($boutique->phone)
                                    <div class="flex items-center gap-2">
                                        <i data-lucide="phone" class="w-4 h-4"></i>
                                        <a href="tel:{{ $boutique->phone }}" class="hover:text-primary dark:hover:text-primary-400">{{ $boutique->phone }}</a>
                                    </div>
                        <!-- Content -->
                        <div class="p-6 flex-1 flex flex-col justify-between">
                            <div>
                                <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2 line-clamp-1 group-hover:text-primary transition-colors">
                                    {{ $boutique->name }}
                                </h3>

                                <div class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400 mb-3">
                                    <i data-lucide="map-pin" class="w-4 h-4 text-primary shrink-0"></i>
                                    <span class="line-clamp-1">{{ $boutique->location ?? $boutique->address ?? 'Localisation non spécifiée' }}</span>
                                </div>

                                @if($boutique->description)
                                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-4 line-clamp-2 leading-relaxed">
                                        {{ $boutique->description }}
                                    </p>
                                @endif
                                @if($boutique->email)
                                    <div class="flex items-center gap-2">
                                        <i data-lucide="mail" class="w-4 h-4"></i>
                                        <a href="mailto:{{ $boutique->email }}" class="hover:text-primary dark:hover:text-primary-400">{{ $boutique->email }}</a>
                                    </div>
                                @endif
                            </div>

                            <a href="{{ route($routePrefix . '.show', $boutique) }}" class="inline-flex items-center gap-2 text-primary dark:text-primary-400 hover:text-primary-700 dark:hover:text-primary-300 font-medium text-sm">
                                Voir les détails
                                <i data-lucide="arrow-right" class="w-4 h-4"></i>
                            </a>
                            <!-- Meta & Action -->
                            <div class="pt-4 border-t border-gray-100 dark:border-gray-700 flex items-center justify-between">
                                <div class="flex items-center gap-4 text-xs font-medium text-gray-500 dark:text-gray-400">
                                    @if($boutique->surface)
                                        <span class="flex items-center gap-1">
                                            <i data-lucide="maximize" class="w-3.5 h-3.5"></i> {{ $boutique->surface }} m²
                                        </span>
                                    @endif
                                    @if($boutique->rooms)
                                        <span class="flex items-center gap-1">
                                            <i data-lucide="door-open" class="w-3.5 h-3.5"></i> {{ $boutique->rooms }} pièce(s)
                                        </span>
                                    @endif
                                </div>

                                <a href="{{ route('boutique.show', $boutique) }}" class="inline-flex items-center gap-1 text-sm font-semibold text-primary hover:text-primary-700 transition-colors">
                                    Détails <i data-lucide="arrow-right" class="w-4 h-4"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Pagination -->
            <div class="mt-12">
                {{ $boutiques->links() }}
            </div>
        @else
            <div class="text-center py-12">
                <i data-lucide="inbox" class="w-12 h-12 text-gray-400 dark:text-gray-600 mx-auto mb-4"></i>
                <h3 class="text-lg font-medium text-gray-800 dark:text-white mb-2">Aucune boutique trouvée</h3>
                <p class="text-gray-600 dark:text-gray-400">Essayez une autre recherche ou revenir plus tard</p>
            <div class="text-center py-16 bg-card dark:bg-gray-800 rounded-2xl border border-dashed border-gray-200 dark:border-gray-700">
                <i data-lucide="store" class="w-12 h-12 text-gray-400 dark:text-gray-600 mx-auto mb-4"></i>
                <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-1">Aucune boutique disponible</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400">Essayez de modifier vos filtres de recherche.</p>
            </div>
        @endif
    </div>
@endsection
