@extends('layouts.artisan')

@section('title', 'Nouvelle attestation')

@section('breadcrumbs')
    <nav class="flex items-center gap-2 text-sm text-muted-foreground">
        <a href="{{ route('artisan.documents.index') }}" class="hover:text-red-500 transition-colors">
            <i data-lucide="folder" class="w-4 h-4"></i>
            <span>Documents</span>
        </a>
        <i data-lucide="chevron-right" class="w-4 h-4"></i>
        <span>Nouvelle attestation</span>
    </nav>
@endsection

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="bg-card rounded-xl shadow-sm border border-border p-6 transition-all duration-300">
        <!-- En-tête -->
        <div class="flex items-center gap-3 mb-6">
            <div class="p-2.5 rounded-full bg-red-50 dark:bg-red-900/20">
                <i data-lucide="stamp" class="w-6 h-6 text-red-600 dark:text-red-400"></i>
            </div>
            <div>
                <h1 class="text-xl font-bold text-foreground">Nouvelle attestation</h1>
                <p class="text-sm text-muted-foreground">Créez une attestation pour votre client</p>
            </div>
        </div>

        <form action="{{ route('artisan.documents.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="type" value="attestation">

            <!-- Champs communs -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                <div>
                    <label class="block text-sm font-medium text-foreground mb-1">Nom du document *</label>
                    <input type="text" name="nom" value="{{ old('nom') }}" required
                           class="w-full rounded-lg border-border focus:border-red-500 focus:ring-red-500 bg-background text-sm @error('nom') border-red-500 @enderror">
                    @error('nom') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-foreground mb-1">Client *</label>
                    <select name="client_id" required
                            class="w-full rounded-lg border-border focus:border-red-500 focus:ring-red-500 bg-background text-sm @error('client_id') border-red-500 @enderror">
                        <option value="">Sélectionnez un client</option>
                        @foreach($clients as $client)
                            <option value="{{ $client->id }}" {{ old('client_id') == $client->id ? 'selected' : '' }}>{{ $client->nom }}</option>
                        @endforeach
                    </select>
                    @error('client_id') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-foreground mb-1">Projet (optionnel)</label>
                    <select name="chantier_id"
                            class="w-full rounded-lg border-border focus:border-red-500 focus:ring-red-500 bg-background text-sm">
                        <option value="">Aucun projet</option>
                        @foreach($chantiers as $chantier)
                            <option value="{{ $chantier->id }}" {{ old('chantier_id') == $chantier->id ? 'selected' : '' }}>{{ $chantier->nom }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-foreground mb-1">Fichier (optionnel)</label>
                    <input type="file" name="fichier"
                           accept=".pdf,.jpg,.jpeg,.png,.doc,.docx"
                           class="w-full rounded-lg border-border focus:border-red-500 focus:ring-red-500 bg-background text-sm @error('fichier') border-red-500 @enderror">
                    <p class="text-xs text-muted-foreground mt-1">PDF, JPG, PNG, DOC, DOCX (max 10MB) — Un PDF sera généré automatiquement</p>
                    @error('fichier') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <!-- Informations de l'attestation -->
            <div class="border-t border-border pt-4 mb-6">
                <h3 class="text-sm font-semibold text-foreground mb-3">Informations de l'attestation</h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-foreground mb-1">Référence (optionnel)</label>
                        <input type="text" name="reference_attestation" value="{{ old('reference_attestation') }}"
                               class="w-full rounded-lg border-border focus:border-red-500 focus:ring-red-500 bg-background text-sm @error('reference_attestation') border-red-500 @enderror">
                        @error('reference_attestation') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-foreground mb-1">Date d'émission *</label>
                        <input type="date" name="date_emission_attestation" value="{{ old('date_emission_attestation') }}" required
                               class="w-full rounded-lg border-border focus:border-red-500 focus:ring-red-500 bg-background text-sm @error('date_emission_attestation') border-red-500 @enderror">
                        @error('date_emission_attestation') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-foreground mb-1">Titre de l'attestation *</label>
                        <input type="text" name="titre_attestation" value="{{ old('titre_attestation') }}" required
                               class="w-full rounded-lg border-border focus:border-red-500 focus:ring-red-500 bg-background text-sm @error('titre_attestation') border-red-500 @enderror"
                               placeholder="Ex : Attestation de travaux réalisés">
                        @error('titre_attestation') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-foreground mb-1">Description *</label>
                        <textarea name="description_attestation" rows="4" required
                                  class="w-full rounded-lg border-border focus:border-red-500 focus:ring-red-500 bg-background text-sm @error('description_attestation') border-red-500 @enderror"
                                  placeholder="Décrivez le contenu de l'attestation...">{{ old('description_attestation') }}</textarea>
                        @error('description_attestation') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            <!-- Boutons d'action -->
            <div class="border-t border-border pt-4">
                <div class="flex gap-3">
                    <button type="submit"
                            class="px-6 py-2 bg-red-500 hover:bg-red-600 text-white rounded-lg text-sm font-medium transition flex items-center gap-2">
                        <i data-lucide="save" class="w-4 h-4"></i>
                        Enregistrer l'attestation
                    </button>
                    <a href="{{ route('artisan.documents.index') }}"
                       class="px-6 py-2 bg-muted hover:bg-muted/80 text-foreground rounded-lg text-sm font-medium transition">
                        Annuler
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection