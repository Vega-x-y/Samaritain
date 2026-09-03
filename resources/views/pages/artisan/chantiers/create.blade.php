@extends('layouts.artisan')

@section('title', 'Nouveau chantier - Artisan')

@section('breadcrumbs')
    <nav class="flex items-center gap-2 text-sm text-muted-foreground">
        <a href="{{ route('artisan.chantiers.index') }}" class="hover:text-primary transition-colors">
            <i data-lucide="hard-hat" class="w-4 h-4"></i>
            <span>Mes chantiers</span>
        </a>
        <i data-lucide="chevron-right" class="w-4 h-4"></i>
        <span>Nouveau chantier</span>
    </nav>
@endsection

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- En-tête -->
    <div class="mb-8">
        <h1 class="text-xl md:text-2xl font-bold text-gray-900 dark:text-white"><i data-lucide="clipboard-list" class="w-4 h-4 inline-block align-middle mr-1"></i> Nouveau <span class="text-primary">chantier</span></h1>
        <p class="text-gray-500 dark:text-gray-400 mt-1 text-sm">Créez un nouveau projet</p>
    </div>

    <div class="max-w-3xl">
        <form method="POST" action="{{ route('artisan.chantiers.store') }}" class="space-y-6">
            @csrf

            <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-sm border border-gray-100 dark:border-gray-700">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Informations générales</h2>

                <div class="space-y-4">
                    <div>
                        <label class="block font-semibold text-sm mb-1 text-gray-900 dark:text-white">Nom du projet *</label>
                        <input type="text" name="nom" value="{{ old('nom') }}" required
                            class="w-full px-4 py-2.5 border border-gray-200 dark:border-gray-600 rounded-lg focus:border-primary focus:ring-2 focus:ring-primary/10 dark:focus:ring-primary/20 outline-none transition bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                        @error('nom') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block font-semibold text-sm mb-1 text-gray-900 dark:text-white">Client</label>
                        <x-form.select name="client_id" label="Client" placeholder="Sélectionner un client" :options="$clients" optionValue="id" optionLabel="nom" />
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block font-semibold text-sm mb-1 text-gray-900 dark:text-white">Type *</label>
                            <x-form.select name="type" label="Type *" :options="$types" required />
                        </div>

                        <div>
                            <label class="block font-semibold text-sm mb-1 text-gray-900 dark:text-white">Statut</label>
                            <x-form.select name="statut" label="Statut" :options="$statuts->mapWithKeys(fn ($statut) => [$statut->value => $statut->label()])" />
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block font-semibold text-sm mb-1 text-gray-900 dark:text-white">Budget (FCFA HT)</label>
                            <input type="number" name="budget" value="{{ old('budget') }}"
                                class="w-full px-4 py-2.5 border border-gray-200 dark:border-gray-600 rounded-lg focus:border-primary focus:ring-2 focus:ring-primary/10 dark:focus:ring-primary/20 outline-none transition bg-white dark:bg-gray-700 text-gray-900 dark:text-white" min="0" step="100">
                            @error('budget') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block font-semibold text-sm mb-1 text-gray-900 dark:text-white">Priorité</label>
                            <x-form.select name="priorite" label="Priorité" :options="['haute' => 'Haute', 'moyenne' => 'Moyenne', 'basse' => 'Basse']" placeholder="Priorité" />
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block font-semibold text-sm mb-1 text-gray-900 dark:text-white">Date de début</label>
                            <input type="date" name="date_debut" value="{{ old('date_debut') }}"
                                class="w-full px-4 py-2.5 border border-gray-200 dark:border-gray-600 rounded-lg focus:border-primary focus:ring-2 focus:ring-primary/10 dark:focus:ring-primary/20 outline-none transition bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                            @error('date_debut') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block font-semibold text-sm mb-1 text-gray-900 dark:text-white">Date de fin</label>
                            <input type="date" name="date_fin" value="{{ old('date_fin') }}"
                                class="w-full px-4 py-2.5 border border-gray-200 dark:border-gray-600 rounded-lg focus:border-primary focus:ring-2 focus:ring-primary/10 dark:focus:ring-primary/20 outline-none transition bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                            @error('date_fin') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-sm border border-gray-100 dark:border-gray-700">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Détails supplémentaires</h2>

                <div class="space-y-4">
                    <div>
                        <label class="block font-semibold text-sm mb-1 text-gray-900 dark:text-white">Matériel nécessaire</label>
                        <textarea name="materiel" rows="3"
                            class="w-full px-4 py-2.5 border border-gray-200 dark:border-gray-600 rounded-lg focus:border-primary focus:ring-2 focus:ring-primary/10 dark:focus:ring-primary/20 outline-none transition bg-white dark:bg-gray-700 text-gray-900 dark:text-white">{{ old('materiel') }}</textarea>
                        @error('materiel') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block font-semibold text-sm mb-1 text-gray-900 dark:text-white">Note client</label>
                        <textarea name="note_client" rows="3"
                            class="w-full px-4 py-2.5 border border-gray-200 dark:border-gray-600 rounded-lg focus:border-primary focus:ring-2 focus:ring-primary/10 dark:focus:ring-primary/20 outline-none transition bg-white dark:bg-gray-700 text-gray-900 dark:text-white">{{ old('note_client') }}</textarea>
                        @error('note_client') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            <div class="flex gap-3">
                <button type="submit" class="flex-1 bg-primary hover:bg-primary/90 text-white py-3 rounded-xl font-semibold transition shadow-md">
                    Créer le chantier
                </button>
                <a href="{{ route('artisan.chantiers.index') }}" class="px-6 py-3 border border-gray-200 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-xl font-semibold hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                    Annuler
                </a>
            </div>
        </form>
    </div>
</div>
@endsection