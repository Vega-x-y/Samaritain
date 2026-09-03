@extends('layouts.artisan')

@section('title', 'Modifier chantier - Artisan')

@section('breadcrumbs')
    <nav class="flex items-center gap-2 text-sm text-muted-foreground">
        <a href="{{ route('artisan.chantiers.index') }}" class="hover:text-primary transition-colors">
            <i data-lucide="hard-hat" class="w-4 h-4"></i>
            <span>Mes chantiers</span>
        </a>
        <i data-lucide="chevron-right" class="w-4 h-4"></i>
        <span>Modifier chantier</span>
    </nav>
@endsection

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- En-tête -->
    <div class="mb-8">
        <h1 class="text-xl md:text-2xl font-bold text-gray-900 dark:text-white"><i data-lucide="pencil" class="w-4 h-4 inline-block align-middle mr-1"></i> Modifier le <span class="text-primary">chantier</span></h1>
        <p class="text-gray-500 dark:text-gray-400 mt-1 text-sm">Modifiez les informations du projet</p>
    </div>

    <div class="max-w-3xl">
        <form method="POST" action="{{ route('artisan.chantiers.update', $chantier) }}" class="space-y-6">
            @csrf
            @method('PUT')

            <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-sm border border-gray-100 dark:border-gray-700">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Informations générales</h2>

                <div class="space-y-4">
                    <div>
                        <label class="block font-semibold text-sm mb-1 text-gray-900 dark:text-white">Nom du projet *</label>
                        <input type="text" name="nom" value="{{ old('nom', $chantier->nom) }}" required
                            class="w-full px-4 py-2.5 border border-gray-200 dark:border-gray-600 rounded-lg focus:border-primary focus:ring-2 focus:ring-primary/10 dark:focus:ring-primary/20 outline-none transition bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                        @error('nom') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block font-semibold text-sm mb-1 text-gray-900 dark:text-white">Client</label>
                        <select name="client_id"
                            class="w-full px-4 py-2.5 border border-gray-200 dark:border-gray-600 rounded-lg focus:border-primary focus:ring-2 focus:ring-primary/10 dark:focus:ring-primary/20 outline-none transition bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                            <option value="">-- Sélectionner un client --</option>
                            @foreach ($clients as $client)
                                <option value="{{ $client->id }}" {{ old('client_id', $chantier->client_id) == $client->id ? 'selected' : '' }}>{{ $client->nom }}</option>
                            @endforeach
                        </select>
                        @error('client_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block font-semibold text-sm mb-1 text-gray-900 dark:text-white">Type *</label>
                            <select name="type" required
                                class="w-full px-4 py-2.5 border border-gray-200 dark:border-gray-600 rounded-lg focus:border-primary focus:ring-2 focus:ring-primary/10 dark:focus:ring-primary/20 outline-none transition bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                                @foreach ($types as $value => $label)
                                    <option value="{{ $value }}" {{ old('type', $chantier->type) === $value ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('type') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block font-semibold text-sm mb-1 text-gray-900 dark:text-white">Statut</label>
                            <select name="statut"
                                class="w-full px-4 py-2.5 border border-gray-200 dark:border-gray-600 rounded-lg focus:border-primary focus:ring-2 focus:ring-primary/10 dark:focus:ring-primary/20 outline-none transition bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                                @foreach ($statuts as $statut)
                                    <option value="{{ $statut->value }}" {{ old('statut', $chantier->statut?->value) === $statut->value ? 'selected' : '' }}>{{ $statut->label() }}</option>
                                @endforeach
                            </select>
                            @error('statut') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block font-semibold text-sm mb-1 text-gray-900 dark:text-white">Budget (FCFA HT)</label>
                            <input type="number" name="budget" value="{{ old('budget', $chantier->budget) }}"
                                class="w-full px-4 py-2.5 border border-gray-200 dark:border-gray-600 rounded-lg focus:border-primary focus:ring-2 focus:ring-primary/10 dark:focus:ring-primary/20 outline-none transition bg-white dark:bg-gray-700 text-gray-900 dark:text-white" min="0" step="100">
                            @error('budget') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block font-semibold text-sm mb-1 text-gray-900 dark:text-white">Priorité</label>
                            <select name="priorite"
                                class="w-full px-4 py-2.5 border border-gray-200 dark:border-gray-600 rounded-lg focus:border-primary focus:ring-2 focus:ring-primary/10 dark:focus:ring-primary/20 outline-none transition bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                                <option value="">-- Priorité --</option>
                                <option value="haute" {{ old('priorite', $chantier->priorite) === 'haute' ? 'selected' : '' }}><i data-lucide="package" class="w-4 h-4 inline-block align-middle mr-1"></i> Haute</option>
                                <option value="moyenne" {{ old('priorite', $chantier->priorite) === 'moyenne' ? 'selected' : '' }}>Moyenne</option>
                                <option value="basse" {{ old('priorite', $chantier->priorite) === 'basse' ? 'selected' : '' }}>Basse</option>
                            </select>
                            @error('priorite') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block font-semibold text-sm mb-1 text-gray-900 dark:text-white">Date de début</label>
                            <input type="date" name="date_debut" value="{{ old('date_debut', $chantier->date_debut?->format('Y-m-d')) }}"
                                class="w-full px-4 py-2.5 border border-gray-200 dark:border-gray-600 rounded-lg focus:border-primary focus:ring-2 focus:ring-primary/10 dark:focus:ring-primary/20 outline-none transition bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                            @error('date_debut') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block font-semibold text-sm mb-1 text-gray-900 dark:text-white">Date de fin</label>
                            <input type="date" name="date_fin" value="{{ old('date_fin', $chantier->date_fin?->format('Y-m-d')) }}"
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
                            class="w-full px-4 py-2.5 border border-gray-200 dark:border-gray-600 rounded-lg focus:border-primary focus:ring-2 focus:ring-primary/10 dark:focus:ring-primary/20 outline-none transition bg-white dark:bg-gray-700 text-gray-900 dark:text-white">{{ old('materiel', $chantier->materiel) }}</textarea>
                        @error('materiel') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block font-semibold text-sm mb-1 text-gray-900 dark:text-white">Note client</label>
                        <textarea name="note_client" rows="3"
                            class="w-full px-4 py-2.5 border border-gray-200 dark:border-gray-600 rounded-lg focus:border-primary focus:ring-2 focus:ring-primary/10 dark:focus:ring-primary/20 outline-none transition bg-white dark:bg-gray-700 text-gray-900 dark:text-white">{{ old('note_client', $chantier->note_client) }}</textarea>
                        @error('note_client') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            <div class="flex gap-3">
                <button type="submit" class="flex-1 bg-primary hover:bg-primary/90 text-white py-3 rounded-xl font-semibold transition shadow-md">
                    Mettre à jour
                </button>
                <a href="{{ route('artisan.chantiers.index') }}" class="px-6 py-3 border border-gray-200 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-xl font-semibold hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                    Annuler
                </a>
            </div>
        </form>
    </div>
</div>
@if(session('reload_kpis'))
    <script>setTimeout(() => location.reload(), 100);</script>
@endif
@endsection
