@extends('layouts.artisan')

@section('title', 'Nouvelle facture')

@section('breadcrumbs')
    <nav class="flex items-center gap-2 text-sm text-muted-foreground">
        <a href="{{ route('artisan.documents.index') }}" class="hover:text-primary transition-colors">
            <i data-lucide="folder" class="w-4 h-4"></i>
            <span>Documents</span>
        </a>
        <i data-lucide="chevron-right" class="w-4 h-4"></i>
        <span>Nouvelle facture</span>
    </nav>
@endsection

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="bg-card rounded-xl shadow-sm border border-border p-6 transition-all duration-300">
        <!-- En-tête -->
        <div class="flex items-center gap-3 mb-6">
            <div class="p-2.5 rounded-full bg-blue-50 dark:bg-blue-900/20">
                <i data-lucide="receipt" class="w-6 h-6 text-blue-600 dark:text-blue-400"></i>
            </div>
            <div>
                <h1 class="text-xl font-bold text-foreground">Nouvelle facture</h1>
                <p class="text-sm text-muted-foreground">Créez une facture pour votre client</p>
            </div>
        </div>

        <form action="{{ route('artisan.documents.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="type" value="facture">

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

            <!-- Informations de la facture -->
            <div class="border-t border-border pt-4 mb-6">
                <h3 class="text-sm font-semibold text-foreground mb-3">Informations de la facture</h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-foreground mb-1">Numéro de facture *</label>
                        <input type="text" name="numero_facture" value="{{ old('numero_facture') }}" required
                               class="w-full rounded-lg border-border focus:border-primary focus:ring-primary bg-background text-sm @error('numero_facture') border-red-500 @enderror">
                        @error('numero_facture') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-foreground mb-1">Date d'émission *</label>
                        <input type="date" name="date_emission_facture" value="{{ old('date_emission_facture') }}" required
                               class="w-full rounded-lg border-border focus:border-primary focus:ring-primary bg-background text-sm @error('date_emission_facture') border-red-500 @enderror">
                        @error('date_emission_facture') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-foreground mb-1">Montant HT (FCFA) *</label>
                        <input type="number" name="montant_ht" id="montant_ht" value="{{ old('montant_ht') }}"
                               step="0.01" min="0" required
                               class="w-full rounded-lg border-border focus:border-primary focus:ring-primary bg-background text-sm @error('montant_ht') border-red-500 @enderror">
                        @error('montant_ht') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-foreground mb-1">TVA (%)</label>
                        <input type="number" name="tva" id="tva" value="{{ old('tva', 0) }}"
                               step="0.01" min="0"
                               class="w-full rounded-lg border-border focus:border-primary focus:ring-primary bg-background text-sm @error('tva') border-red-500 @enderror">
                        @error('tva') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-foreground mb-1">Montant TTC (FCFA) *</label>
                        <input type="number" name="montant_ttc" id="montant_ttc" value="{{ old('montant_ttc') }}"
                               step="0.01" min="0" required readonly
                               class="w-full rounded-lg border-border focus:border-primary focus:ring-primary bg-background text-sm @error('montant_ttc') border-red-500 @enderror">
                        <p class="text-xs text-muted-foreground mt-1">Calculé automatiquement</p>
                        @error('montant_ttc') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            <!-- Boutons d'action -->
            <div class="border-t border-border pt-4">
                <div class="flex gap-3">
                    <button type="submit"
                            class="px-6 py-2 bg-primary hover:bg-primary/90 text-white rounded-lg text-sm font-medium transition flex items-center gap-2">
                        <i data-lucide="save" class="w-4 h-4"></i>
                        Enregistrer la facture
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
    function calculateTTC() {
        const ht = parseFloat(document.getElementById('montant_ht').value) || 0;
        const tva = parseFloat(document.getElementById('tva').value) || 0;
        const ttc = ht + (ht * tva / 100);
        document.getElementById('montant_ttc').value = ttc.toFixed(2);
    }

    document.getElementById('montant_ht').addEventListener('input', calculateTTC);
    document.getElementById('tva').addEventListener('input', calculateTTC);

    // Calcul initial
    calculateTTC();
</script>
@endpush
@endsection