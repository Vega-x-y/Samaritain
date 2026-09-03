@extends('layouts.artisan')

@section('title', 'Nouveau compte rendu')

@section('breadcrumbs')
    <nav class="flex items-center gap-2 text-sm text-muted-foreground">
        <a href="{{ route('artisan.documents.index') }}" class="hover:text-primary transition-colors">
            <i data-lucide="folder" class="w-4 h-4"></i>
            <span>Documents</span>
        </a>
        <i data-lucide="chevron-right" class="w-4 h-4"></i>
        <span>Nouveau compte rendu</span>
    </nav>
@endsection

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="bg-card rounded-xl shadow-sm border border-border p-6 transition-all duration-300">
        <!-- En-tête -->
        <div class="flex items-center gap-3 mb-6">
            <div class="p-2.5 rounded-full bg-primary/10 dark:bg-primary/20">
                <i data-lucide="clipboard-list" class="w-6 h-6 text-primary dark:text-primary"></i>
            </div>
            <div>
                <h1 class="text-xl font-bold text-foreground">Nouveau compte rendu</h1>
                <p class="text-sm text-muted-foreground">Créez un compte rendu d'intervention pour votre client</p>
            </div>
        </div>

        <form action="{{ route('artisan.documents.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="type" value="compte_rendu">

            <!-- Champs communs -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                <div>
                    <label class="block text-sm font-medium text-foreground mb-1">Nom du document *</label>
                    <input type="text" name="nom" value="{{ old('nom') }}" required
                           class="w-full rounded-lg border-border focus:border-primary focus:ring-primary bg-background text-sm @error('nom') border-red-500 @enderror">
                    @error('nom') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-foreground mb-1">Client *</label>
                    <select name="client_id" required
                            class="w-full rounded-lg border-border focus:border-primary focus:ring-primary bg-background text-sm @error('client_id') border-red-500 @enderror">
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
                            class="w-full rounded-lg border-border focus:border-primary focus:ring-primary bg-background text-sm">
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
                           class="w-full rounded-lg border-border focus:border-primary focus:ring-primary bg-background text-sm @error('fichier') border-red-500 @enderror">
                    <p class="text-xs text-muted-foreground mt-1">PDF, JPG, PNG, DOC, DOCX (max 10MB) �?" Un PDF sera généré automatiquement</p>
                    @error('fichier') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <!-- Informations du compte rendu -->
            <div class="border-t border-border pt-4 mb-6">
                <h3 class="text-sm font-semibold text-foreground mb-3">Informations du compte rendu</h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-foreground mb-1">Titre du compte rendu *</label>
                        <input type="text" name="titre_compte_rendu" value="{{ old('titre_compte_rendu') }}" required
                               class="w-full rounded-lg border-border focus:border-primary focus:ring-primary bg-background text-sm @error('titre_compte_rendu') border-red-500 @enderror">
                        @error('titre_compte_rendu') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-foreground mb-1">Description *</label>
                        <textarea name="description_compte_rendu" rows="3" required
                                  class="w-full rounded-lg border-border focus:border-primary focus:ring-primary bg-background text-sm @error('description_compte_rendu') border-red-500 @enderror"
                                  placeholder="Décrivez les travaux effectués...">{{ old('description_compte_rendu') }}</textarea>
                        @error('description_compte_rendu') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-foreground mb-1">Date d'intervention *</label>
                        <input type="date" name="date_intervention" value="{{ old('date_intervention') }}" required
                               class="w-full rounded-lg border-border focus:border-primary focus:ring-primary bg-background text-sm @error('date_intervention') border-red-500 @enderror">
                        @error('date_intervention') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-foreground mb-1">Durée (heures) *</label>
                        <input type="number" name="duree" value="{{ old('duree') }}"
                               step="0.5" min="0.5" required
                               class="w-full rounded-lg border-border focus:border-primary focus:ring-primary bg-background text-sm @error('duree') border-red-500 @enderror">
                        @error('duree') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-foreground mb-1">Photos avant (optionnel)</label>
                        <input type="file" name="photos_avant"
                               accept="image/*"
                               class="w-full rounded-lg border-border focus:border-primary focus:ring-primary bg-background text-sm">
                        @error('photos_avant') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-foreground mb-1">Photos après (optionnel)</label>
                        <input type="file" name="photos_apres"
                               accept="image/*"
                               class="w-full rounded-lg border-border focus:border-primary focus:ring-primary bg-background text-sm">
                        @error('photos_apres') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            <!-- Boutons d'action -->
            <div class="border-t border-border pt-4">
                <div class="flex gap-3">
                    <button type="submit"
                            class="px-6 py-2 bg-primary hover:bg-primary/90 text-white rounded-lg text-sm font-medium transition flex items-center gap-2">
                        <i data-lucide="save" class="w-4 h-4"></i>
                        Enregistrer le compte rendu
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