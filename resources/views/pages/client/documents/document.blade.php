@extends('layouts.base')

@section('title', $document->type_label.' à consulter')

@section('content')
    <x-ui.user-dashboard-nav />
    <x-blade-components::layout.container>
        <div class="container mx-auto px-4 py-8">
            <!-- En-tête -->
            <div class="mb-8">
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $document->type_label }} à consulter</h1>
                <p class="text-gray-500 dark:text-gray-400 mt-1 text-sm">
                    Consultez et téléchargez le document partagé par votre artisan.
                </p>
            </div>

            <!-- Carte du document -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden mb-6">
                <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                    <div class="flex items-center justify-between">
                        <div>
                            <h2 class="text-xl font-semibold text-gray-900 dark:text-white">{{ $document->nom }}</h2>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                                @if($document->type === 'facture')
                                    Numéro : {{ $document->metadata['numero'] ?? 'N/A' }}
                                    · Date d'émission : {{ $document->metadata['date_emission'] ?? 'N/A' }}
                                @elseif($document->type === 'compte_rendu')
                                    Titre : {{ $document->metadata['titre'] ?? 'N/A' }}
                                    · Date d'intervention : {{ $document->metadata['date_intervention'] ?? 'N/A' }}
                                @endif
                            </p>
                        </div>
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium
                            @if($document->type === 'facture') bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300
                            @else bg-orange-100 text-orange-800 dark:bg-orange-900/30 dark:text-orange-300 @endif">
                            {{ $document->type_label }}
                        </span>
                    </div>
                </div>

                <div class="p-6">
                    <!-- Affichage du fichier PDF -->
                    <div class="mb-6">
                        <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-3">Document</h3>
                        @if($document->mime_type === 'application/pdf')
                            <iframe src="{{ $document->url }}"
                                    class="w-full h-96 border border-gray-200 dark:border-gray-700 rounded-lg"
                                    title="{{ $document->type_label }} PDF"></iframe>
                        @else
                            <a href="{{ $document->url }}" target="_blank"
                               class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition">
                                <i data-lucide="eye" class="w-4 h-4"></i>
                                Voir le document
                            </a>
                        @endif
                    </div>

                    <!-- Métadonnées du document -->
                    @if($document->metadata)
                        <div class="mb-6">
                            <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-3">Détails du document</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                                @if($document->type === 'facture')
                                    @if($document->metadata['numero'] ?? null)
                                        <div>
                                            <span class="text-gray-500 dark:text-gray-400">Numéro :</span>
                                            <span class="text-gray-900 dark:text-white font-medium">{{ $document->metadata['numero'] }}</span>
                                        </div>
                                    @endif
                                    @if($document->metadata['date_emission'] ?? null)
                                        <div>
                                            <span class="text-gray-500 dark:text-gray-400">Date d'émission :</span>
                                            <span class="text-gray-900 dark:text-white font-medium">{{ $document->metadata['date_emission'] }}</span>
                                        </div>
                                    @endif
                                    @if($document->metadata['montant_ht'] ?? null)
                                        <div>
                                            <span class="text-gray-500 dark:text-gray-400">Montant HT :</span>
                                            <span class="text-gray-900 dark:text-white font-medium">{{ number_format($document->metadata['montant_ht'], 0, ',', ' ') }} FCFA</span>
                                        </div>
                                    @endif
                                    @if($document->metadata['tva'] ?? null)
                                        <div>
                                            <span class="text-gray-500 dark:text-gray-400">TVA :</span>
                                            <span class="text-gray-900 dark:text-white font-medium">{{ $document->metadata['tva'] }}%</span>
                                        </div>
                                    @endif
                                    @if($document->metadata['montant_ttc'] ?? null)
                                        <div>
                                            <span class="text-gray-500 dark:text-gray-400">Montant TTC :</span>
                                            <span class="text-gray-900 dark:text-white font-medium">{{ number_format($document->metadata['montant_ttc'], 0, ',', ' ') }} FCFA</span>
                                        </div>
                                    @endif
                                @elseif($document->type === 'compte_rendu')
                                    @if($document->metadata['titre'] ?? null)
                                        <div>
                                            <span class="text-gray-500 dark:text-gray-400">Titre :</span>
                                            <span class="text-gray-900 dark:text-white font-medium">{{ $document->metadata['titre'] }}</span>
                                        </div>
                                    @endif
                                    @if($document->metadata['date_intervention'] ?? null)
                                        <div>
                                            <span class="text-gray-500 dark:text-gray-400">Date d'intervention :</span>
                                            <span class="text-gray-900 dark:text-white font-medium">{{ $document->metadata['date_intervention'] }}</span>
                                        </div>
                                    @endif
                                    @if($document->metadata['duree'] ?? null)
                                        <div>
                                            <span class="text-gray-500 dark:text-gray-400">Durée :</span>
                                            <span class="text-gray-900 dark:text-white font-medium">{{ $document->metadata['duree'] }} heure(s)</span>
                                        </div>
                                    @endif
                                    @if($document->metadata['description'] ?? null)
                                        <div class="md:col-span-2">
                                            <span class="text-gray-500 dark:text-gray-400">Description :</span>
                                            <p class="text-gray-900 dark:text-white mt-1">{{ $document->metadata['description'] }}</p>
                                        </div>
                                    @endif
                                @endif
                            </div>
                        </div>
                    @endif

                    <!-- Boutons d'action -->
                    <div class="flex gap-3">
                        <a href="{{ $document->url }}" download
                           class="inline-flex items-center gap-2 px-6 py-2 bg-orange-500 hover:bg-orange-600 text-white rounded-lg text-sm font-medium transition">
                            <i data-lucide="download" class="w-4 h-4"></i>
                            Télécharger
                        </a>
                        <a href="{{ route('client.documents.index') }}"
                           class="inline-flex items-center gap-2 px-6 py-2 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-900 dark:text-white rounded-lg text-sm font-medium transition">
                            <i data-lucide="arrow-left" class="w-4 h-4"></i>
                            Retour
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </x-blade-components::layout.container>
@endsection