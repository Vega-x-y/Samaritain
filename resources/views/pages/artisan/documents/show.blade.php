@extends('layouts.artisan')

@section('title', $document->nom)

@section('breadcrumbs')
    <nav class="flex items-center gap-2 text-sm text-muted-foreground">
        <i data-lucide="folder" class="w-4 h-4"></i>
        <a href="{{ route('artisan.documents.index') }}" class="hover:text-foreground">Documents</a>
        <i data-lucide="chevron-right" class="w-4 h-4"></i>
        <span>{{ $document->nom }}</span>
    </nav>
@endsection

@section('content')
<div class="space-y-6">
    <!-- En-tête -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-foreground">{{ $document->nom }}</h1>
            <p class="text-sm text-muted-foreground mt-1">
                Client : <span class="font-medium">{{ $document->client->nom ?? 'N/A' }}</span>
                · Créé le {{ $document->created_at->format('d/m/Y') }}
            </p>
        </div>
        <div class="flex items-center gap-2">
            @if($document->isDevis())
                @php
                    $statusColors = [
                        'draft' => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
                        'sent' => 'bg-orange-100 text-orange-800 dark:bg-orange-900/30 dark:text-orange-300',
                        'signed' => 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300',
                    ];
                @endphp
                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ $statusColors[$document->status] ?? $statusColors['draft'] }}">
                    {{ $document->status_label }}
                </span>
            @endif
        </div>
    </div>

    <!-- Carte principale -->
    <div class="bg-card rounded-lg shadow-sm border border-border p-6">
        <!-- Affichage du fichier PDF -->
        <div class="mb-6">
            <h3 class="text-sm font-semibold text-foreground mb-3">Document</h3>
            @if($document->mime_type === 'application/pdf')
                <iframe src="{{ $document->url }}"
                        class="w-full h-[600px] border border-border rounded-lg"
                        title="{{ $document->nom }}"></iframe>
            @else
                <a href="{{ $document->url }}" target="_blank"
                   class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition">
                    <i data-lucide="eye" class="w-4 h-4"></i>
                    Voir le document
                </a>
            @endif
        </div>

        <!-- Métadonnées du devis -->
        @if($document->metadata && $document->isDevis())
            <div class="border-t border-border pt-6">
                <h3 class="text-sm font-semibold text-foreground mb-3">Détails du devis</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                    @if($document->metadata['reference'] ?? null)
                        <div>
                            <span class="text-muted-foreground">Référence :</span>
                            <span class="text-foreground font-medium ml-2">{{ $document->metadata['reference'] }}</span>
                        </div>
                    @endif
                    @if($document->metadata['date_emission'] ?? null)
                        <div>
                            <span class="text-muted-foreground">Date d'émission :</span>
                            <span class="text-foreground font-medium ml-2">{{ $document->metadata['date_emission'] }}</span>
                        </div>
                    @endif
                    @if($document->metadata['conditions_generales'] ?? null)
                        <div class="md:col-span-2">
                            <span class="text-muted-foreground">Conditions générales :</span>
                            <p class="text-foreground mt-1">{{ $document->metadata['conditions_generales'] }}</p>
                        </div>
                    @endif
                </div>

                @if(isset($document->metadata['lignes']) && count($document->metadata['lignes']) > 0)
                    <div class="mt-4">
                        <h4 class="text-sm font-medium text-foreground mb-2">Lignes du devis</h4>
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead>
                                    <tr class="border-b border-border">
                                        <th class="text-left py-2 text-muted-foreground">Libellé</th>
                                        <th class="text-right py-2 text-muted-foreground">Qté</th>
                                        <th class="text-right py-2 text-muted-foreground">Prix unitaire (FCFA)</th>
                                        <th class="text-right py-2 text-muted-foreground">Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php $grandTotal = 0; @endphp
                                    @foreach($document->metadata['lignes'] as $ligne)
                                        @php
                                            $total = ($ligne['quantite'] ?? 0) * ($ligne['prix_unitaire'] ?? 0);
                                            $grandTotal += $total;
                                        @endphp
                                        <tr class="border-b border-border/50">
                                            <td class="py-2 text-foreground">{{ $ligne['libelle'] ?? '' }}</td>
                                            <td class="py-2 text-right text-foreground">{{ $ligne['quantite'] ?? 0 }}</td>
                                            <td class="py-2 text-right text-foreground">{{ number_format($ligne['prix_unitaire'] ?? 0, 0, ',', ' ') }}</td>
                                            <td class="py-2 text-right text-foreground font-medium">{{ number_format($total, 0, ',', ' ') }}</td>
                                        </tr>
                                    @endforeach
                                    <tr class="border-t-2 border-border font-semibold">
                                        <td colspan="3" class="py-2 text-right text-foreground">Total :</td>
                                        <td class="py-2 text-right text-foreground">{{ number_format($grandTotal, 0, ',', ' ') }} FCFA</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif
            </div>
        @endif

        <!-- Section Acceptation -->
        @if($document->isDevis())
            <div class="border-t border-border pt-6 mt-6">
                <h3 class="text-lg font-semibold text-foreground mb-4">Acceptation du devis</h3>

                @if($document->isSigned())
                    <!-- Devis accepté -->
                    <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-6">
                        <div class="flex items-start gap-4">
                            <div class="flex-shrink-0">
                                <div class="w-16 h-16 rounded-full bg-green-100 dark:bg-green-800 flex items-center justify-center">
                                    <i data-lucide="check" class="w-8 h-8 text-green-600 dark:text-green-400"></i>
                                </div>
                            </div>
                            <div class="flex-1">
                                <h4 class="text-base font-semibold text-green-900 dark:text-green-100 mb-2">
                                    Devis accepté par le client
                                </h4>
                                <div class="space-y-2 text-sm text-green-800 dark:text-green-200">
                                    <p>
                                        <span class="font-medium">Accepté par :</span>
                                        {{ $document->signature_data['signed_by_client_id'] ? ($document->client->nom ?? 'Client #'.$document->signature_data['signed_by_client_id']) : 'N/A' }}
                                    </p>
                                    <p>
                                        <span class="font-medium">Date d'acceptation :</span>
                                        {{ $document->signed_at?->format('d/m/Y H:i') ?? 'N/A' }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                @else
                    <!-- En attente d'acceptation -->
                    <div class="bg-orange-50 dark:bg-orange-900/20 border border-orange-200 dark:border-orange-800 rounded-lg p-6">
                        <div class="flex items-center gap-3">
                            <i data-lucide="clock" class="w-6 h-6 text-orange-600 dark:text-orange-400"></i>
                            <div>
                                <p class="text-sm font-medium text-orange-900 dark:text-orange-100">
                                    En attente d'acceptation client
                                </p>
                                <p class="text-xs text-orange-700 dark:text-orange-300 mt-1">
                                    Le devis a été envoyé au client. Vous recevrez une notification une fois accepté.
                                </p>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        @endif

        <!-- Actions -->
        <div class="border-t border-border pt-6 mt-6 flex items-center gap-3">
            <a href="{{ route('artisan.documents.index') }}"
               class="px-4 py-2 bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 rounded-lg text-sm font-medium transition">
                <i data-lucide="arrow-left" class="w-4 h-4 inline mr-2"></i>
                Retour à la liste
            </a>

            @if($document->canExport())
                <a href="{{ route('artisan.documents.export-pdf', $document) }}"
                   class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg text-sm font-medium transition inline-flex items-center gap-2">
                    <i data-lucide="download" class="w-4 h-4"></i>
                    Exporter le PDF
                </a>
            @endif

            <a href="{{ $document->url }}" target="_blank"
               class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm font-medium transition inline-flex items-center gap-2">
                <i data-lucide="eye" class="w-4 h-4"></i>
                Voir le document original
            </a>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Initialiser les icônes Lucide
    lucide.createIcons();
</script>
@endpush