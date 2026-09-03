@extends('layouts.artisan')

@section('title', 'Nouvel événement - Artisan')

@section('breadcrumbs')
    <nav class="flex items-center gap-2 text-sm text-muted-foreground">
        <a href="{{ route('artisan.planning.index') }}" class="hover:text-primary transition-colors">
            <i data-lucide="calendar" class="w-4 h-4"></i>
            <span>Planning</span>
        </a>
        <i data-lucide="chevron-right" class="w-4 h-4"></i>
        <span>Nouvel événement</span>
    </nav>
@endsection

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- En-tête -->
    <div class="mb-8">
        <h1 class="text-xl md:text-2xl font-bold text-gray-900 dark:text-white"><i data-lucide="calendar-days" class="w-4 h-4 inline-block align-middle mr-1"></i> Nouvel <span class="text-primary">événement</span></h1>
        <p class="text-gray-500 dark:text-gray-400 mt-1 text-sm">Planifiez un nouveau rendez-vous ou intervention</p>
    </div>

    <div class="max-w-3xl">
        <form method="POST" action="{{ route('artisan.planning.store') }}" class="space-y-6">
            @csrf

            <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-sm border border-gray-100 dark:border-gray-700">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Informations de l'événement</h2>

                <div class="space-y-4">
                    <div>
                        <label class="block font-semibold text-sm mb-1 text-gray-900 dark:text-white">Titre *</label>
                        <input type="text" name="titre" value="{{ old('titre') }}" required
                            class="w-full px-4 py-2.5 border border-gray-200 dark:border-gray-600 rounded-lg focus:border-primary focus:ring-2 focus:ring-primary/10 dark:focus:ring-primary/20 outline-none transition bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                        @error('titre') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block font-semibold text-sm mb-1 text-gray-900 dark:text-white">Chantier associé</label>
                        <x-form.select name="chantier_id" label="Chantier associé" placeholder="Aucun chantier" :options="$chantiers" optionValue="id" optionLabel="nom" />
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block font-semibold text-sm mb-1 text-gray-900 dark:text-white">Date et heure de début *</label>
                            <input type="datetime-local" name="date_debut" value="{{ old('date_debut') }}" required
                                class="w-full px-4 py-2.5 border border-gray-200 dark:border-gray-600 rounded-lg focus:border-primary focus:ring-2 focus:ring-primary/10 dark:focus:ring-primary/20 outline-none transition bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                            @error('date_debut') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block font-semibold text-sm mb-1 text-gray-900 dark:text-white">Date et heure de fin *</label>
                            <input type="datetime-local" name="date_fin" value="{{ old('date_fin') }}" required
                                class="w-full px-4 py-2.5 border border-gray-200 dark:border-gray-600 rounded-lg focus:border-primary focus:ring-2 focus:ring-primary/10 dark:focus:ring-primary/20 outline-none transition bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                            @error('date_fin') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div>
                        <label class="block font-semibold text-sm mb-1 text-gray-900 dark:text-white">Type *</label>
                        <x-form.select name="type" label="Type *" :options="$types->mapWithKeys(fn ($type) => [$type->value => $type->label()])" required />
            <div class="flex gap-3">
                <button type="submit" class="flex-1 bg-primary hover:bg-primary/90 text-white py-3 rounded-xl font-semibold transition shadow-md hover:shadow-lg">
                    Créer l'événement
                </button>
                <a href="{{ route('artisan.planning.index') }}" class="px-6 py-3 border border-gray-200 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-xl font-semibold hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                    Annuler
                </a>
            </div>
        </form>
    </div>
</div>
@endsection