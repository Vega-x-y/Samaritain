@extends('layouts.base')

@section('title', $hotel->title)

@section('content')
    <div class="container mx-auto px-4 sm:px-6 lg:px-8 py-6 md:py-8">
        <!-- Fil d'ariane -->
        <nav class="flex items-center gap-2 text-sm text-muted-foreground dark:text-gray-400 mb-6">
            <a href="{{ route('index') }}" class="hover:text-primary dark:hover:text-primary-400 transition-colors">Accueil</a>
            <i data-lucide="chevron-right" class="w-3 h-3"></i>
            <a href="{{ route('hotel.index') }}" class="hover:text-primary dark:hover:text-primary-400 transition-colors">Hôtels</a>
            <i data-lucide="chevron-right" class="w-3 h-3"></i>
            <span class="text-foreground dark:text-gray-300">{{ $hotel->title }}</span>
        </nav>

        <!-- Galerie -->
        <x-ui.hotel-gallery :hotel="$hotel" />

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Contenu principal -->
            <div class="lg:col-span-2">
                <div class="flex items-start justify-between gap-4 mb-4">
                    <div>
                        <h1 class="text-2xl md:text-3xl font-bold text-foreground dark:text-white">{{ $hotel->title }}</h1>
                        <p class="text-muted-foreground dark:text-gray-400 mt-1 flex items-center gap-1">
                            <i data-lucide="map-pin" class="w-4 h-4"></i>
                            {{ $hotel->arrondissement->name ?? '' }}, {{ $hotel->city->name }}
                        </p>
                    </div>
                    <div class="flex items-center gap-1 text-amber-500">
                        @for($i = 1; $i <= 5; $i++)
                            <i data-lucide="star" class="w-5 h-5 {{ $i <= $hotel->star_rating ? 'fill-amber-500' : 'text-gray-300' }}"></i>
                        @endfor
                    </div>
                </div>

                @if($hotel->is_verify)
                    <span class="inline-flex items-center gap-1 bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300 text-xs font-medium px-2.5 py-1 rounded-full mb-4">
                        <i data-lucide="badge-check" class="w-3 h-3"></i> Vérifié
                    </span>
                @endif

                <div class="prose dark:prose-invert max-w-none mb-8">
                    <h2 class="text-lg font-semibold text-foreground dark:text-white mb-2">Description</h2>
                    <p class="text-muted-foreground dark:text-gray-400 leading-relaxed">{{ $hotel->description }}</p>
                </div>

                <!-- Caractéristiques -->
                <div class="mb-8">
                    <h2 class="text-lg font-semibold text-foreground dark:text-white mb-4">Caractéristiques</h2>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <div class="p-4 bg-card dark:bg-gray-800 rounded-xl border border-border dark:border-gray-700 text-center">
                            <i data-lucide="bed-double" class="w-6 h-6 text-primary mx-auto mb-2"></i>
                            <p class="text-sm font-medium text-foreground dark:text-white">{{ $hotel->rooms }} chambre(s)</p>
                        </div>
                        <div class="p-4 bg-card dark:bg-gray-800 rounded-xl border border-border dark:border-gray-700 text-center">
                            <i data-lucide="bath" class="w-6 h-6 text-primary mx-auto mb-2"></i>
                            <p class="text-sm font-medium text-foreground dark:text-white">{{ $hotel->bathrooms }} salle(s) de bain</p>
                        </div>
                        <div class="p-4 bg-card dark:bg-gray-800 rounded-xl border border-border dark:border-gray-700 text-center">
                            <i data-lucide="star" class="w-6 h-6 text-primary mx-auto mb-2"></i>
                            <p class="text-sm font-medium text-foreground dark:text-white">{{ $hotel->star_rating }} étoile(s)</p>
                        </div>
                        <div class="p-4 bg-card dark:bg-gray-800 rounded-xl border border-border dark:border-gray-700 text-center">
                            <i data-lucide="sofa" class="w-6 h-6 text-primary mx-auto mb-2"></i>
                            <p class="text-sm font-medium text-foreground dark:text-white">{{ $hotel->furnished ? 'Meublé' : 'Non meublé' }}</p>
                        </div>
                    </div>
                </div>

                <!-- Équipements -->
                @if($hotel->amenities->isNotEmpty())
                    <div class="mb-8">
                        <h2 class="text-lg font-semibold text-foreground dark:text-white mb-4">Équipements</h2>
                        <div class="flex flex-wrap gap-2">
                            @foreach($hotel->amenities as $amenity)
                                <span class="inline-flex items-center gap-1 px-3 py-1.5 bg-primary/10 dark:bg-primary-400/20 text-primary dark:text-primary-400 text-sm rounded-full">
                                    <i data-lucide="check" class="w-3 h-3"></i>
                                    {{ $amenity->name }}
                                </span>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>

            <!-- Sidebar -->
            <div>
                <div class="bg-card dark:bg-gray-800 rounded-2xl border border-border dark:border-gray-700 p-6 sticky top-24">
                    <p class="text-2xl font-bold text-foreground dark:text-white mb-1">
                        {{ number_format($hotel->price_per_hour, 0, ',', ' ') }} FCFA
                        <span class="text-sm font-normal text-muted-foreground dark:text-gray-400">{{ $hotel->price_label }}</span>
                    </p>
                    <p class="text-sm text-muted-foreground dark:text-gray-400 mb-4">{{ $hotel->address }}</p>

                    @if($hotel->contact)
                        <a href="tel:{{ $hotel->contact }}"
                            class="w-full inline-flex items-center justify-center gap-2 px-6 py-3 bg-green-600 text-white rounded-xl font-semibold hover:bg-green-700 transition mb-3">
                            <i data-lucide="phone" class="w-4 h-4"></i>
                            Contacter l'hôtel
                        </a>
                    @endif

                    <div class="flex items-center gap-2 text-sm text-muted-foreground dark:text-gray-400 mb-4">
                        <i data-lucide="eye" class="w-4 h-4"></i>
                        {{ number_format($hotel->views) }} vues
                    </div>

                    <a href="{{ route('hotel.index') }}"
                        class="w-full inline-flex items-center justify-center gap-2 px-6 py-3 bg-primary dark:bg-primary-600 text-white rounded-xl font-semibold hover:bg-primary/90 dark:hover:bg-primary-700 transition">
                        <i data-lucide="arrow-left" class="w-4 h-4"></i>
                        Retour aux hôtels
                    </a>
                </div>
            </div>
        </div>

        <!-- Hôtels similaires -->
        @if($similarHotels->isNotEmpty())
            <div class="mt-12">
                <h2 class="text-xl md:text-2xl font-bold text-foreground dark:text-white mb-6">Hôtels similaires</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($similarHotels as $similar)
                        <x-ui.hotel-card :hotel="$similar" />
                    @endforeach
                </div>
            </div>
        @endif
    </div>
@endsection