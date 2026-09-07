@extends('layouts.dashboard')

@section('title', 'Détails de la boutique - ' . $boutique->name)

@section('content')
    @php
        $adminRoutePrefix = 'admin.boutique';
    @endphp
    <div class="space-y-6">
        <!-- En-tête avec retour et actions -->
        <div class="flex justify-between items-center">
            <div class="flex items-center gap-4">
                <a href="{{ route($adminRoutePrefix . '.index') }}" class="text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white">
                    <i data-lucide="arrow-left" class="w-5 h-5"></i>
                </a>
                <h1 class="text-gray-800 dark:text-white">Boutique : {{ $boutique->name }}</h1>
            </div>
            <div class="flex gap-2">
                <x-btn href="{{ route($adminRoutePrefix . '.edit', $boutique) }}" style="outline" class="dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 dark:hover:bg-gray-700">
                    <x-slot:prefix>
                        <i data-lucide="edit" class="w-4 h-4"></i>
                    </x-slot:prefix>
                    Modifier
                </x-btn>
            </div>
        </div>

        <x-container-dashed>
            <!-- Badges de statut -->
            <div class="flex gap-2 mb-6 pb-4 border-b border-gray-100 dark:border-gray-700">
                @if($boutique->active)
                    <span class="px-3 py-1 text-xs font-medium text-green-600 dark:text-green-400 bg-green-100 dark:bg-green-900/30 rounded-full">
                        <i data-lucide="check-circle" class="inline w-3 h-3"></i> Actif
                    </span>
                @else
                    <span class="px-3 py-1 text-xs font-medium text-red-600 dark:text-red-400 bg-red-100 dark:bg-red-900/30 rounded-full">
                        <i data-lucide="eye-off" class="inline w-3 h-3"></i> Inactif
                    </span>
                @endif
            </div>

            <!-- Informations principales -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div>
                    <h3 class="text-sm font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wide mb-2">Nom</h3>
                    <p class="text-gray-800 dark:text-white">{{ $boutique->name }}</p>
                </div>
                <div>
                    <h3 class="text-sm font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wide mb-2">Localisation</h3>
                    <p class="text-gray-800 dark:text-white flex items-center gap-2">
                        <i data-lucide="map-pin" class="w-4 h-4"></i>
                        {{ $boutique->location ?? 'Non spécifiée' }}
                    </p>
                </div>
            </div>

            <!-- Contact -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div>
                    <h3 class="text-sm font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wide mb-2">Téléphone</h3>
                    <p class="text-gray-800 dark:text-white flex items-center gap-2">
                        <i data-lucide="phone" class="w-4 h-4"></i>
                        <a href="tel:{{ $boutique->phone }}" class="text-blue-600 dark:text-blue-400 hover:underline">
                            {{ $boutique->phone ?? 'Non spécifié' }}
                        </a>
                    </p>
                </div>
                <div>
                    <h3 class="text-sm font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wide mb-2">Email</h3>
                    <p class="text-gray-800 dark:text-white flex items-center gap-2">
                        <i data-lucide="mail" class="w-4 h-4"></i>
                        <a href="mailto:{{ $boutique->email }}" class="text-blue-600 dark:text-blue-400 hover:underline">
                            {{ $boutique->email ?? 'Non spécifié' }}
                        </a>
                    </p>
                </div>
            </div>

            <!-- Description -->
            @if($boutique->description)
                <div class="mb-6">
                    <h3 class="text-sm font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wide mb-2">Description</h3>
                    <p class="text-gray-800 dark:text-white whitespace-pre-line">{{ $boutique->description }}</p>
                </div>
            @endif

            <!-- Métadonnées -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-6 border-t border-gray-100 dark:border-gray-700">
                <div>
                    <h3 class="text-sm font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wide mb-2">Créée le</h3>
                    <p class="text-gray-800 dark:text-white">{{ $boutique->created_at->format('d/m/Y H:i') }}</p>
                </div>
                <div>
                    <h3 class="text-sm font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wide mb-2">Modifiée le</h3>
                    <p class="text-gray-800 dark:text-white">{{ $boutique->updated_at->format('d/m/Y H:i') }}</p>
                </div>
            </div>
        </x-container-dashed>
    </div>
@endsection
