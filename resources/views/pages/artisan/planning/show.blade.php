@extends('layouts.artisan')

@section('title', $evenement->titre.' - Planning - Artisan')

@section('breadcrumbs')
    <nav class="flex items-center gap-2 text-sm text-muted-foreground">
        <a href="{{ route('artisan.planning.index') }}" class="hover:text-foreground transition-colors flex items-center gap-1">
            <i data-lucide="calendar" class="w-4 h-4"></i>
            <span>Planning</span>
        </a>
        <span class="text-muted-foreground">/</span>
        <span class="text-foreground font-medium truncate max-w-48">{{ $evenement->titre }}</span>
    </nav>
@endsection

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- En-tête -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-8">
        <div class="flex items-center gap-4">
            <div class="w-16 h-16 rounded-full bg-primary/10 dark:bg-primary/20 flex items-center justify-center text-2xl font-bold text-primary dark:text-primary">
                <i data-lucide="calendar-days" class="w-4 h-4 inline-block align-middle mr-1"></i>
            </div>
            <div>
                <h1 class="text-xl md:text-2xl font-bold text-gray-900 dark:text-white">{{ $evenement->titre }}</h1>
                <p class="text-gray-500 dark:text-gray-400 mt-1 text-sm">
                    {{ $evenement->type->icon() }} {{ $evenement->type->label() }}
                </p>
            </div>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('artisan.planning.edit', $evenement) }}" class="px-4 py-2 rounded-full text-sm font-medium border border-blue-300 text-blue-600 hover:bg-blue-50 dark:border-blue-800 dark:text-blue-400 dark:hover:bg-blue-900/20 transition">
                <i data-lucide="pencil" class="w-4 h-4 inline-block align-middle mr-1"></i> Modifier
            </a>
            <form method="POST" action="{{ route('artisan.planning.destroy', $evenement) }}" onsubmit="return confirm('Supprimer cet événement ?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="px-4 py-2 rounded-full text-sm font-medium border border-red-300 text-red-600 hover:bg-red-50 dark:border-red-800 dark:text-red-400 dark:hover:bg-red-900/20 transition">
                    Supprimer
                </button>
            </form>
            <a href="{{ route('artisan.planning.index') }}" class="px-4 py-2 rounded-full text-sm font-medium border border-gray-300 text-gray-600 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-400 dark:hover:bg-gray-800 transition">
                �?� Retour
            </a>
        </div>
    </div>

    <!-- Grille d'information -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Informations générales -->
        <div class="bg-white dark:bg-gray-800 rounded-xl p-5 border border-gray-100 dark:border-gray-700">
            <h3 class="text-xs uppercase font-semibold text-gray-400 dark:text-gray-500 mb-3 border-b border-gray-100 dark:border-gray-700 pb-2">Informations</h3>
            <div class="space-y-2 text-sm">
                <div class="flex justify-between">
                    <span class="text-gray-500 dark:text-gray-400">Type</span>
                    <span class="text-xs font-semibold px-2 py-0.5 rounded-full {{ $evenement->type->colorClass() }}">
                        {{ $evenement->type->icon() }} {{ $evenement->type->label() }}
                    </span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500 dark:text-gray-400">Date de début</span>
                    <span class="font-medium text-gray-900 dark:text-white">{{ $evenement->date_debut->format('d/m/Y H:i') }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500 dark:text-gray-400">Date de fin</span>
                    <span class="font-medium text-gray-900 dark:text-white">{{ $evenement->date_fin->format('d/m/Y H:i') }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500 dark:text-gray-400">Durée</span>
                    <span class="font-medium text-gray-900 dark:text-white">{{ $evenement->duree }} min</span>
                </div>
                @if ($evenement->chantier)
                    <div class="flex justify-between">
                        <span class="text-gray-500 dark:text-gray-400">Chantier</span>
                        <a href="{{ route('artisan.chantiers.show', $evenement->chantier) }}" class="font-medium text-primary hover:text-primary transition-colors">
                            {{ $evenement->chantier->nom }}
                        </a>
                    </div>
                @endif
            </div>
        </div>

        <!-- Description -->
        @if ($evenement->description)
        <div class="bg-white dark:bg-gray-800 rounded-xl p-5 border border-gray-100 dark:border-gray-700">
            <h3 class="text-xs uppercase font-semibold text-gray-400 dark:text-gray-500 mb-3 border-b border-gray-100 dark:border-gray-700 pb-2"><i data-lucide="package" class="w-4 h-4 inline-block align-middle mr-1"></i> Description</h3>
            <p class="text-sm text-gray-700 dark:text-gray-300 whitespace-pre-wrap">{{ $evenement->description }}</p>
        </div>
        @endif
    </div>
</div>
@endsection