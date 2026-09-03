
@extends('layouts.artisan')
@section('title', 'Finances - ' . $chantier->nom)

@section('breadcrumbs')
    <nav class="flex items-center gap-2 text-sm text-muted-foreground">
        <a href="{{ route('artisan.finances.index') }}" class="hover:text-primary transition-colors">
            <i data-lucide="wallet" class="w-4 h-4"></i>
            <span>Finances</span>
        </a>
        <i data-lucide="chevron-right" class="w-4 h-4"></i>
        <span>{{ $chantier->nom }}</span>
    </nav>
@endsection

@section('content')
    <div class="space-y-6">
        <!-- Résumé financier -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-card rounded-lg shadow-sm border border-border p-6 transition-all duration-300 hover:shadow-lg hover:-translate-y-1">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-muted-foreground">Chiffre d'affaires</p>
                         <p class="text-3xl font-bold text-green-600 mt-2">{{ number_format($totalCA, 2, ',', ' ') }} FCFA</p>
                    </div>
                    <div class="p-3 bg-green-100 dark:bg-green-900/30 rounded-lg">
                        <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-card rounded-lg shadow-sm border border-border p-6 transition-all duration-300 hover:shadow-lg hover:-translate-y-1">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-muted-foreground">Dépenses totales</p>
                         <p class="text-3xl font-bold text-red-600 mt-2">{{ number_format($totalDepenses, 2, ',', ' ') }} FCFA</p>
                    </div>
                    <div class="p-3 bg-red-100 dark:bg-red-900/30 rounded-lg">
                        <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6"/>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-card rounded-lg shadow-sm border border-border p-6 transition-all duration-300 hover:shadow-lg hover:-translate-y-1">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-muted-foreground">Rentabilité</p>
                        <p class="text-3xl font-bold mt-2 {{ $rentabilite >= 0 ? 'text-green-600' : 'text-red-600' }}">
                             {{ number_format($rentabilite, 2, ',', ' ') }} FCFA
                        </p>
                    </div>
                    <div class="p-3 {{ $rentabilite >= 0 ? 'bg-green-100 dark:bg-green-900/30' : 'bg-red-100 dark:bg-red-900/30' }} rounded-lg">
                        <svg class="w-8 h-8 {{ $rentabilite >= 0 ? 'text-green-600' : 'text-red-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Onglets -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 transition-all duration-300 hover:shadow-lg">
            <div class="border-b border-gray-200">
                <nav class="flex -mb-px">
                    <button onclick="showTab('devis')" id="tab-devis" class="tab-button active px-6 py-4 text-sm font-medium border-b-2 border-primary text-primary">
                        Devis ({{ $chantier->devis->count() }})
                    </button>
                    <button onclick="showTab('factures')" id="tab-factures" class="tab-button px-6 py-4 text-sm font-medium border-b-2 border-transparent text-muted-foreground hover:text-foreground">
                        Factures ({{ $chantier->factures->count() }})
                    </button>
                    <button onclick="showTab('depenses')" id="tab-depenses" class="tab-button px-6 py-4 text-sm font-medium border-b-2 border-transparent text-muted-foreground hover:text-foreground">
                        Dépenses ({{ $chantier->depenses->count() }})
                    </button>
                    <button onclick="showTab('transactions')" id="tab-transactions" class="tab-button px-6 py-4 text-sm font-medium border-b-2 border-transparent text-muted-foreground hover:text-foreground">
                        Transactions ({{ $chantier->transactions->count() }})
                    </button>
                </nav>
            </div>

            <div class="p-6">
                <!-- Section Devis -->
                <div id="content-devis" class="tab-content">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold text-gray-900">Devis</h3>
                        <button onclick="toggleForm('form-devis')" class="px-4 py-2 bg-primary hover:bg-primary/90 text-white rounded-lg transition-all duration-200 hover:scale-105 active:scale-95">
                            + Nouveau devis
                        </button>
                    </div>

                    <!-- Formulaire devis -->
                    <div id="form-devis" class="hidden mb-6 bg-gray-50 rounded-lg p-6">
                        <form action="{{ route('artisan.finances.store-devis', $chantier) }}" method="POST">
                            @csrf
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Numéro *</label>
                            <input type="text" name="numero" required class="w-full rounded-lg border-border focus:border-primary focus:ring-primary bg-background">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Statut *</label>
                            <select name="statut" required class="w-full rounded-lg border-border focus:border-primary focus:ring-primary bg-background">
                                        <option value="brouillon">Brouillon</option>
                                        <option value="envoye">Envoyé</option>
                                        <option value="signe">Signé</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Date d'envoi</label>
                            <input type="date" name="date_envoi" class="w-full rounded-lg border-border focus:border-primary focus:ring-primary bg-background">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Date de signature</label>
                            <input type="date" name="date_signature" class="w-full rounded-lg border-border focus:border-primary focus:ring-primary bg-background">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Montant HT</label>
                            <input type="number" step="0.01" name="montant_ht" class="w-full rounded-lg border-border focus:border-primary focus:ring-primary bg-background">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">TVA</label>
                            <input type="number" step="0.01" name="tva" class="w-full rounded-lg border-border focus:border-primary focus:ring-primary bg-background">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Montant TTC</label>
                            <input type="number" step="0.01" name="montant_ttc" class="w-full rounded-lg border-border focus:border-primary focus:ring-primary bg-background">
                                </div>
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Notes</label>
                            <textarea name="notes" rows="3" class="w-full rounded-lg border-border focus:border-primary focus:ring-primary bg-background"></textarea>
                                </div>
                            </div>
                            <div class="flex gap-3 mt-4">
                                <button type="submit" class="px-4 py-2 bg-primary hover:bg-primary/90 text-white rounded-lg transition">
                                    Créer
                                </button>
                                <button type="button" onclick="toggleForm('form-devis')" class="px-4 py-2 bg-muted hover:bg-muted/80 text-foreground rounded-lg transition">
                                    Annuler
                                </button>
                            </div>
                        </form>
                    </div>

                    @if($chantier->devis->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Numéro</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Statut</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date envoi</th>
                                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Montant TTC</th>
                                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    @foreach($chantier->devis as $devis)
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $devis->numero }}</td>
                                            <td class="px-4 py-3">
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                                    {{ $devis->statut === 'signe' ? 'bg-green-100 text-green-800' : 
                                                       ($devis->statut === 'envoye' ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-100 text-gray-800') }}">
                                                    {{ ucfirst($devis->statut) }}
                                                </span>
                                            </td>
                                            <td class="px-4 py-3 text-sm text-gray-900">{{ $devis->date_envoi?->format('d/m/Y') ?? '-' }}</td>
                                            <td class="px-4 py-3 text-right text-sm font-medium text-gray-900">
                                                 {{ $devis->montant_ttc ? number_format($devis->montant_ttc, 2, ',', ' ') . ' FCFA' : '-' }}
                                            </td>
                                            <td class="px-4 py-3 text-center">
                                            <button onclick="editDevis({{ $devis->id }})" class="text-primary hover:text-primary text-sm">Modifier</button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-gray-500 text-center py-8">Aucun devis pour ce chantier</p>
                    @endif
                </div>

                <!-- Section Factures -->
                <div id="content-factures" class="tab-content hidden">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold text-gray-900">Factures</h3>
                        <button onclick="toggleForm('form-facture')" class="px-4 py-2 bg-primary hover:bg-primary/90 text-white rounded-lg transition-all duration-200 hover:scale-105 active:scale-95">
                            + Nouvelle facture
                        </button>
                    </div>

                    <!-- Formulaire facture -->
                    <div id="form-facture" class="hidden mb-6 bg-gray-50 rounded-lg p-6">
                        <form action="{{ route('artisan.finances.store-facture', $chantier) }}" method="POST">
                            @csrf
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Numéro *</label>
                                    <input type="text" name="numero" required class="w-full rounded-lg border-border focus:border-primary focus:ring-primary bg-background">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Statut *</label>
                                    <select name="statut" required class="w-full rounded-lg border-border focus:border-primary focus:ring-primary bg-background">
                                        <option value="brouillon">Brouillon</option>
                                        <option value="envoyee">Envoyée</option>
                                        <option value="payee">Payée</option>
                                        <option value="annulee">Annulée</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Montant HT *</label>
                                    <input type="number" step="0.01" name="montant_ht" required class="w-full rounded-lg border-border focus:border-primary focus:ring-primary bg-background">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">TVA</label>
                                    <input type="number" step="0.01" name="tva" class="w-full rounded-lg border-border focus:border-primary focus:ring-primary bg-background">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Montant TTC *</label>
                                    <input type="number" step="0.01" name="montant_ttc" required class="w-full rounded-lg border-border focus:border-primary focus:ring-primary bg-background">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Date d'émission *</label>
                                    <input type="date" name="date_emission" required class="w-full rounded-lg border-border focus:border-primary focus:ring-primary bg-background">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Date d'échéance</label>
                                    <input type="date" name="date_echeance" class="w-full rounded-lg border-border focus:border-primary focus:ring-primary bg-background">
                                </div>
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                                    <textarea name="description" rows="3" class="w-full rounded-lg border-border focus:border-primary focus:ring-primary bg-background"></textarea>
                                </div>
                            </div>
                            <div class="flex gap-3 mt-4">
                                <button type="submit" class="px-4 py-2 bg-primary hover:bg-primary/90 text-white rounded-lg transition">
                                    Créer
                                </button>
                                <button type="button" onclick="toggleForm('form-facture')" class="px-4 py-2 bg-muted hover:bg-muted/80 text-foreground rounded-lg transition">
                                    Annuler
                                </button>
                            </div>
                        </form>
                    </div>

                    @if($chantier->factures->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Numéro</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Statut</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date émission</th>
                                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Montant TTC</th>
                                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    @foreach($chantier->factures as $facture)
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $facture->numero }}</td>
                                            <td class="px-4 py-3">
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                                    {{ $facture->statut === 'payee' ? 'bg-green-100 text-green-800' : 
                                                       ($facture->statut === 'envoyee' ? 'bg-yellow-100 text-yellow-800' : 
                                                       ($facture->statut === 'annulee' ? 'bg-red-100 text-red-800' : 'bg-gray-100 text-gray-800')) }}">
                                                    {{ ucfirst($facture->statut) }}
                                                </span>
                                            </td>
                                            <td class="px-4 py-3 text-sm text-gray-900">{{ $facture->date_emission->format('d/m/Y') }}</td>
                                            <td class="px-4 py-3 text-right text-sm font-medium text-gray-900">
                                                 {{ number_format($facture->montant_ttc, 2, ',', ' ') }} FCFA
                                            </td>
                                            <td class="px-4 py-3 text-center">
                                            <button onclick="editFacture({{ $facture->id }})" class="text-primary hover:text-primary text-sm">Modifier</button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-gray-500 text-center py-8">Aucune facture pour ce chantier</p>
                    @endif
                </div>

                <!-- Section Dépenses -->
                <div id="content-depenses" class="tab-content hidden">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold text-gray-900">Dépenses</h3>
                        <button onclick="toggleForm('form-depense')" class="px-4 py-2 bg-primary hover:bg-primary/90 text-white rounded-lg transition-all duration-200 hover:scale-105 active:scale-95">
                            + Nouvelle dépense
                        </button>
                    </div>

                    <!-- Formulaire dépense -->
                    <div id="form-depense" class="hidden mb-6 bg-gray-50 rounded-lg p-6">
                        <form action="{{ route('artisan.finances.store-depense', $chantier) }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Catégorie *</label>
                                    <select name="categorie" required class="w-full rounded-lg border-border focus:border-primary focus:ring-primary bg-background">
                                        <option value="materiaux">Matériaux</option>
                                        <option value="main_oeuvre">Main d'oeuvre</option>
                                        <option value="transport">Transport</option>
                                        <option value="autre">Autre</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Montant *</label>
                                    <input type="number" step="0.01" name="montant" required class="w-full rounded-lg border-border focus:border-primary focus:ring-primary bg-background">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Date *</label>
                                    <input type="date" name="date" required class="w-full rounded-lg border-border focus:border-primary focus:ring-primary bg-background">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Fournisseur</label>
                                    <input type="text" name="fournisseur" class="w-full rounded-lg border-border focus:border-primary focus:ring-primary bg-background">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Justificatif</label>
                                    <input type="file" name="justificatif" accept=".pdf,.jpg,.jpeg,.png" class="w-full rounded-lg border-border focus:border-primary focus:ring-primary bg-background">
                                </div>
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                                    <textarea name="description" rows="3" class="w-full rounded-lg border-border focus:border-primary focus:ring-primary bg-background"></textarea>
                                </div>
                            </div>
                            <div class="flex gap-3 mt-4">
                                <button type="submit" class="px-4 py-2 bg-primary hover:bg-primary/90 text-white rounded-lg transition">
                                    Ajouter
                                </button>
                                <button type="button" onclick="toggleForm('form-depense')" class="px-4 py-2 bg-muted hover:bg-muted/80 text-foreground rounded-lg transition">
                                    Annuler
                                </button>
                            </div>
                        </form>
                    </div>

                    @if($chantier->depenses->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Catégorie</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Fournisseur</th>
                                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Montant</th>
                                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    @foreach($chantier->depenses as $depense)
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-4 py-3 text-sm text-gray-900">
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-primary/10 text-primary">
                                                    {{ ucfirst(str_replace('_', ' ', $depense->categorie)) }}
                                                </span>
                                            </td>
                                            <td class="px-4 py-3 text-sm text-gray-900">{{ $depense->date->format('d/m/Y') }}</td>
                                            <td class="px-4 py-3 text-sm text-gray-900">{{ $depense->fournisseur ?? '-' }}</td>
                                            <td class="px-4 py-3 text-right text-sm font-medium text-red-600">
                                                 {{ number_format($depense->montant, 2, ',', ' ') }} FCFA
                                            </td>
                                            <td class="px-4 py-3 text-center">
                                                <button onclick="editDepense({{ $depense->id }})" class="text-primary hover:text-primary text-sm">Modifier</button>
                                                <form action="{{ route('artisan.finances.destroy-depense', $depense) }}" method="POST" class="inline" onsubmit="return confirm('Supprimer cette dépense ?')">
                                                    @csrf @method('DELETE')
                                                    <button type="submit" class="text-red-600 hover:text-red-800 text-sm ml-2">Supprimer</button>
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-gray-500 text-center py-8">Aucune dépense pour ce chantier</p>
                    @endif
                </div>

                <!-- Section Transactions -->
                <div id="content-transactions" class="tab-content hidden">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold text-gray-900">Transactions</h3>
                        <button onclick="toggleForm('form-transaction')" class="px-4 py-2 bg-primary hover:bg-primary/90 text-white rounded-lg transition-all duration-200 hover:scale-105 active:scale-95">
                            + Nouvelle transaction
                        </button>
                    </div>

                    <!-- Formulaire transaction -->
                    <div id="form-transaction" class="hidden mb-6 bg-gray-50 rounded-lg p-6">
                        <form action="{{ route('artisan.finances.store-transaction', $chantier) }}" method="POST">
                            @csrf
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Type *</label>
                                    <select name="type" required class="w-full rounded-lg border-border focus:border-primary focus:ring-primary bg-background">
                                        <option value="acompte">Acompte</option>
                                        <option value="solde">Solde</option>
                                        <option value="remboursement">Remboursement</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Montant *</label>
                                    <input type="number" step="0.01" name="montant" required class="w-full rounded-lg border-border focus:border-primary focus:ring-primary bg-background">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Date *</label>
                                    <input type="date" name="date" required class="w-full rounded-lg border-border focus:border-primary focus:ring-primary bg-background">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Statut *</label>
                                    <select name="statut" required class="w-full rounded-lg border-border focus:border-primary focus:ring-primary bg-background">
                                        <option value="en_attente">En attente</option>
                                        <option value="recu">Reçu</option>
                                        <option value="rembourse">Remboursé</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Référence</label>
                                    <input type="text" name="reference" class="w-full rounded-lg border-border focus:border-primary focus:ring-primary bg-background">
                                </div>
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Notes</label>
                                    <textarea name="notes" rows="3" class="w-full rounded-lg border-border focus:border-primary focus:ring-primary bg-background"></textarea>
                                </div>
                            </div>
                            <div class="flex gap-3 mt-4">
                                <button type="submit" class="px-4 py-2 bg-primary hover:bg-primary/90 text-white rounded-lg transition">
                                    Ajouter
                                </button>
                                <button type="button" onclick="toggleForm('form-transaction')" class="px-4 py-2 bg-muted hover:bg-muted/80 text-foreground rounded-lg transition">
                                    Annuler
                                </button>
                            </div>
                        </form>
                    </div>

                    @if($chantier->transactions->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Statut</th>
                                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Montant</th>
                                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    @foreach($chantier->transactions as $transaction)
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-4 py-3 text-sm text-gray-900">
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                                    {{ ucfirst($transaction->type) }}
                                                </span>
                                            </td>
                                            <td class="px-4 py-3 text-sm text-gray-900">{{ $transaction->date->format('d/m/Y') }}</td>
                                            <td class="px-4 py-3">
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                                    {{ $transaction->statut === 'recu' ? 'bg-green-100 text-green-800' : 
                                                       ($transaction->statut === 'rembourse' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800') }}">
                                                    {{ ucfirst(str_replace('_', ' ', $transaction->statut)) }}
                                                </span>
                                            </td>
                                            <td class="px-4 py-3 text-right text-sm font-medium {{ $transaction->type === 'remboursement' ? 'text-red-600' : 'text-green-600' }}">
                                                 {{ number_format($transaction->montant, 2, ',', ' ') }} FCFA
                                            </td>
                                            <td class="px-4 py-3 text-center">
                                                <button onclick="editTransaction({{ $transaction->id }})" class="text-primary hover:text-primary text-sm">Modifier</button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-gray-500 text-center py-8">Aucune transaction pour ce chantier</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
@if(session('reload_kpis'))
    <script>setTimeout(() => location.reload(), 100);</script>
@endif
@endsection

