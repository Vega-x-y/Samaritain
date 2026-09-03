@extends('layouts.artisan')

@section('title', 'Mon profil')

@section('breadcrumbs')
    <nav class="flex items-center gap-2 text-sm text-muted-foreground">
        <i data-lucide="user" class="w-4 h-4"></i>
        <span>Mon profil</span>
    </nav>
@endsection

@section('content')
    <div class="space-y-6">
        <!-- En-tête du profil -->
        <div class="bg-card rounded-lg shadow-sm border border-border p-6 transition-all duration-300 hover:shadow-lg">
            <div class="flex items-start gap-6">
                <!-- Photo de profil -->
                <div class="shrink-0">
                    @if($artisan->avatar)
                        <img src="{{ asset('storage/' . $artisan->avatar) }}" 
                             alt="{{ $artisan->business_name }}" 
                             class="w-24 h-24 rounded-lg object-cover border border-border transition-transform duration-300 hover:scale-110">
                    @else
                        <div class="w-24 h-24 rounded-lg bg-primary flex items-center justify-center text-white text-3xl font-bold">
                            {{ substr($artisan->business_name, 0, 2) }}
                        </div>
                    @endif
                </div>

                <!-- Informations principales -->
                <div class="flex-1">
                    <h1 class="text-2xl font-bold text-foreground">{{ $artisan->business_name }}</h1>
                    @if($artisan->user)
                        <p class="text-muted-foreground mt-1">
                            {{ $artisan->user->name }}
                        </p>
                    @endif
                    @if($artisan->categories->count() > 0)
                        <div class="flex flex-wrap gap-2 mt-3">
                            @foreach($artisan->categories as $categorie)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-primary/10 text-primary dark:bg-primary/20 dark:text-white/80">
                                    {{ $categorie->name }}
                                </span>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Informations détaillées -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Informations personnelles -->
            <div class="bg-card rounded-lg shadow-sm border border-border p-6 transition-all duration-300 hover:shadow-lg hover:-translate-y-1">
                <h3 class="text-lg font-semibold text-foreground mb-4 flex items-center gap-2">
                    <i data-lucide="user" class="w-5 h-5"></i>
                    Informations personnelles
                </h3>
                <div class="space-y-3">
                    @if($artisan->user)
                        <div class="flex items-start gap-3">
                            <i data-lucide="mail" class="w-4 h-4 text-muted-foreground mt-0.5"></i>
                            <div>
                                <p class="text-xs text-muted-foreground">Email</p>
                                <p class="text-sm text-foreground">{{ $artisan->user->email }}</p>
                            </div>
                        </div>
                    @endif
                    @if($artisan->phone)
                        <div class="flex items-start gap-3">
                            <i data-lucide="phone" class="w-4 h-4 text-muted-foreground mt-0.5"></i>
                            <div>
                                <p class="text-xs text-muted-foreground">Téléphone</p>
                                <p class="text-sm text-foreground">{{ $artisan->phone }}</p>
                            </div>
                        </div>
                    @endif
                    @if($artisan->adresse)
                        <div class="flex items-start gap-3">
                            <i data-lucide="map-pin" class="w-4 h-4 text-muted-foreground mt-0.5"></i>
                            <div>
                                <p class="text-xs text-muted-foreground">Adresse</p>
                                <p class="text-sm text-foreground">{{ $artisan->adresse }}</p>
                            </div>
                        </div>
                    @endif
                    @if($artisan->arrondissement)
                        <div class="flex items-start gap-3">
                            <i data-lucide="map" class="w-4 h-4 text-muted-foreground mt-0.5"></i>
                            <div>
                                <p class="text-xs text-muted-foreground">Localisation</p>
                                <p class="text-sm text-foreground">
                                    {{ $artisan->arrondissement->name }}, {{ $artisan->arrondissement->city->name ?? '' }}
                                </p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Informations professionnelles -->
            <div class="bg-card rounded-lg shadow-sm border border-border p-6 transition-all duration-300 hover:shadow-lg hover:-translate-y-1">
                <h3 class="text-lg font-semibold text-foreground mb-4 flex items-center gap-2">
                    <i data-lucide="briefcase" class="w-5 h-5"></i>
                    Informations professionnelles
                </h3>
                <div class="space-y-3">
                    @if($artisan->description)
                        <div>
                            <p class="text-xs text-muted-foreground mb-1">Description</p>
                            <p class="text-sm text-foreground">{{ $artisan->description }}</p>
                        </div>
                    @endif
                    @if($artisan->site_web)
                        <div class="flex items-start gap-3">
                            <i data-lucide="globe" class="w-4 h-4 text-muted-foreground mt-0.5"></i>
                            <div>
                                <p class="text-xs text-muted-foreground">Site web</p>
                                <a href="{{ $artisan->site_web }}" target="_blank" class="text-sm text-primary hover:text-primary">
                                    {{ $artisan->site_web }}
                                </a>
                            </div>
                        </div>
                    @endif
                    <div class="flex items-start gap-3">
                        <i data-lucide="calendar" class="w-4 h-4 text-muted-foreground mt-0.5"></i>
                        <div>
                            <p class="text-xs text-muted-foreground">Membre depuis</p>
                            <p class="text-sm text-foreground">{{ $artisan->created_at->format('d/m/Y') }}</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3">
                        <i data-lucide="eye" class="w-4 h-4 text-muted-foreground mt-0.5"></i>
                        <div>
                            <p class="text-xs text-muted-foreground">Vues du profil</p>
                            <p class="text-sm text-foreground">{{ $artisan->views }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bouton modifier -->
        <div class="flex justify-center">
            <a href="{{ route('artisan.edit', $artisan) }}" 
               class="inline-flex items-center px-6 py-3 bg-primary hover:bg-primary/90 text-white rounded-lg transition-all duration-200 hover:scale-105 active:scale-95">
                <i data-lucide="settings" class="w-5 h-5 mr-2"></i>
                Modifier mes informations
            </a>
        </div>
    </div>
@endsection