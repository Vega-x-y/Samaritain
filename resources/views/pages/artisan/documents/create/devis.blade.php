@extends('layouts.artisan')

@section('title', 'Nouveau devis')

@section('breadcrumbs')
    <nav class="flex items-center gap-2 text-sm text-muted-foreground">
        <a href="{{ route('artisan.documents.index') }}" class="hover:text-primary transition-colors">
            <i data-lucide="folder" class="w-4 h-4"></i>
            <span>Documents</span>
        </a>
        <i data-lucide="chevron-right" class="w-4 h-4"></i>
        <span>Nouveau devis</span>
    </nav>
@endsection

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="bg-card rounded-xl shadow-sm border border-border p-6 transition-all duration-300">
        <!-- En-tête -->
        <div class="flex items-center gap-3 mb-6">
            <div class="p-2.5 rounded-full bg-green-50 dark:bg-green-900/20">
                <i data-lucide="calculator" class="w-6 h-6 text-green-600 dark:text-green-400"></i>
            </div>
            <div>
                <h1 class="text-xl font-bold text-foreground">Nouveau devis</h1>
                <p class="text-sm text-muted-foreground">Créez un devis détaillé pour votre client</p>
            </div>
        </div>

        <form action="{{ route('artisan.documents.store') }}" method="POST">
            @csrf
            <input type="hidden" name="type" value="devis">

            <!-- Champs communs -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                <div>
                    <label class="block text-sm font-medium text-foreground mb-1">Nom du document</label>
                    <input type="text" name="nom" value="{{ old('nom', 'DEVIS') }}" readonly
                           class="w-full rounded-lg border-border bg-gray-100 dark:bg-gray-700 text-sm text-gray-600 dark:text-gray-400">
                    <p class="text-xs text-muted-foreground mt-1">Nom par défaut</p>
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

            </div>

            <!-- Informations du devis -->
            <div class="border-t border-border pt-4 mb-6">
                <h3 class="text-sm font-semibold text-foreground mb-3">Informations du devis</h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-sm font-medium text-foreground mb-1">Référence devis *</label>
                        <input type="text" name="reference_devis" value="{{ old('reference_devis') }}" required
                               class="w-full rounded-lg border-border focus:border-primary focus:ring-primary bg-background text-sm @error('reference_devis') border-red-500 @enderror">
                        @error('reference_devis') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-foreground mb-1">Date d'émission *</label>
                        <input type="date" name="date_emission_devis" value="{{ old('date_emission_devis') }}" required
                               class="w-full rounded-lg border-border focus:border-primary focus:ring-primary bg-background text-sm @error('date_emission_devis') border-red-500 @enderror">
                        @error('date_emission_devis') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <!-- Lignes du devis -->
                <div class="mb-4">
                    <div class="flex items-center justify-between mb-2">
                        <label class="block text-sm font-medium text-foreground">Lignes du devis *</label>
                        <button type="button" id="addLigneBtn"
                                class="px-3 py-1 bg-green-500 hover:bg-green-600 text-white rounded-lg text-xs transition">
                            + Ajouter une ligne
                        </button>
                    </div>

                    <p class="text-xs text-muted-foreground mb-2" id="lignesHelp">Cliquez sur "Ajouter une ligne" pour commencer.</p>

                    <div id="lignesContainer">
                        <!-- Les lignes seront ajoutées ici par JavaScript -->
                    </div>

                    @error('lignes') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>

                <!-- Conditions générales -->
                <div class="mb-4">
                    <label class="block text-sm font-medium text-foreground mb-1">Conditions générales</label>
                    <textarea name="conditions_generales" rows="3"
                              class="w-full rounded-lg border-border focus:border-primary focus:ring-primary bg-background text-sm"
                              placeholder="Saisissez les conditions générales...">{{ old('conditions_generales') }}</textarea>
                </div>

                <!-- Image des conditions -->
                <div>
                    <label class="block text-sm font-medium text-foreground mb-1">Image des conditions (optionnel)</label>
                    <input type="file" name="conditions_image"
                           accept="image/*"
                           class="w-full rounded-lg border-border focus:border-primary focus:ring-primary bg-background text-sm">
                    @error('conditions_image') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <!-- Boutons d'action -->
            <div class="border-t border-border pt-4">
                <div class="flex gap-3">
                    <button type="submit"
                            class="px-6 py-2 bg-primary hover:bg-primary/90 text-white rounded-lg text-sm font-medium transition flex items-center gap-2">
                        <i data-lucide="save" class="w-4 h-4"></i>
                        Enregistrer le devis
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

@push('scripts')
<script>
    let ligneIndex = 0;

    function addLigne(libelle = '', quantite = 1, prixUnitaire = 0) {
        const container = document.getElementById('lignesContainer');
        const help = document.getElementById('lignesHelp');
        if (help) help.style.display = 'none';

        const div = document.createElement('div');
        div.className = 'grid grid-cols-1 md:grid-cols-4 gap-3 mb-3 items-end';
        div.dataset.index = ligneIndex;

        div.innerHTML = `
            <div class="md:col-span-2">
                <label class="block text-xs text-muted-foreground mb-1">Libellé</label>
                <input type="text"
                       name="lignes[${ligneIndex}][libelle]"
                       value="${libelle}"
                       required
                       class="w-full rounded-lg border-border focus:border-primary focus:ring-primary bg-background text-sm">
            </div>
            <div>
                <label class="block text-xs text-muted-foreground mb-1">Qté</label>
                <input type="number"
                       name="lignes[${ligneIndex}][quantite]"
                       value="${quantite}"
                       step="0.01" min="1" required
                       class="w-full rounded-lg border-border focus:border-primary focus:ring-primary bg-background text-sm">
            </div>
            <div class="flex items-end gap-2">
                <div class="flex-1">
                    <label class="block text-xs text-muted-foreground mb-1">Prix unitaire (FCFA)</label>
                    <input type="number"
                           name="lignes[${ligneIndex}][prix_unitaire]"
                           value="${prixUnitaire}"
                           step="0.01" min="0" required
                           class="w-full rounded-lg border-border focus:border-primary focus:ring-primary bg-background text-sm">
                </div>
                <button type="button"
                        onclick="this.closest('.grid').remove()"
                        class="p-1 text-red-500 hover:text-red-700 rounded">
                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                </button>
            </div>
        `;

        container.appendChild(div);

        // Re-initialize lucide icons for the new element
        if (window.lucide) {
            window.lucide.createIcons();
        }

        ligneIndex++;
    }

    document.getElementById('addLigneBtn').addEventListener('click', () => addLigne());

    // Restaurer les lignes depuis les anciennes valeurs (en cas d'erreur de validation)
    @if(old('lignes'))
        @foreach(old('lignes') as $ligne)
            addLigne('{{ $ligne['libelle'] ?? '' }}', {{ $ligne['quantite'] ?? 1 }}, {{ $ligne['prix_unitaire'] ?? 0 }});
        @endforeach
    @endif
</script>
@endpush
@endsection