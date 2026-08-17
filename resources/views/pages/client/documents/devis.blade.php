@extends('layouts.base')

@section('title', 'Devis à consulter')

@section('content')
    <x-ui.user-dashboard-nav />
    <x-blade-components::layout.container>
        <div class="container mx-auto px-4 py-8">
            <!-- En-tête -->
            <div class="mb-8">
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Devis à consulter</h1>
                <p class="text-gray-500 dark:text-gray-400 mt-1 text-sm">
                    Veuillez revoir le devis ci-dessous, cocher la case d'attestation et le renvoyer à l'artisan.
                </p>
            </div>

            <!-- Carte du devis -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden mb-6">
                <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                    <div class="flex items-center justify-between">
                        <div>
                            <h2 class="text-xl font-semibold text-gray-900 dark:text-white">{{ $document->nom }}</h2>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                                Référence : {{ $document->metadata['reference'] ?? 'N/A' }}
                                · Date d'émission : {{ $document->metadata['date_emission'] ?? 'N/A' }}
                            </p>
                        </div>
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium
                            @if($document->isSigned()) bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300
                            @elseif($document->isSent()) bg-orange-100 text-orange-800 dark:bg-orange-900/30 dark:text-orange-300
                            @else bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300 @endif">
                            {{ $document->status_label }}
                        </span>
                    </div>
                </div>

                <div class="p-6">
                    <!-- Affichage du fichier PDF -->
                    <div class="mb-6">
                        <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-3">Document du devis</h3>
                        @if($document->mime_type === 'application/pdf')
                            <iframe src="{{ $document->url }}"
                                    class="w-full h-96 border border-gray-200 dark:border-gray-700 rounded-lg"
                                    title="Devis PDF"></iframe>
                        @else
                            <a href="{{ $document->url }}" target="_blank"
                               class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition">
                                <i data-lucide="eye" class="w-4 h-4"></i>
                                Voir le document
                            </a>
                        @endif
                    </div>

                    <!-- Métadonnées du devis -->
                    @if($document->metadata)
                        <div class="mb-6">
                            <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-3">Détails du devis</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                                @if($document->metadata['reference'] ?? null)
                                    <div>
                                        <span class="text-gray-500 dark:text-gray-400">Référence :</span>
                                        <span class="text-gray-900 dark:text-white font-medium">{{ $document->metadata['reference'] }}</span>
                                    </div>
                                @endif
                                @if($document->metadata['date_emission'] ?? null)
                                    <div>
                                        <span class="text-gray-500 dark:text-gray-400">Date d'émission :</span>
                                        <span class="text-gray-900 dark:text-white font-medium">{{ $document->metadata['date_emission'] }}</span>
                                    </div>
                                @endif
                                @if($document->metadata['conditions_generales'] ?? null)
                                    <div class="md:col-span-2">
                                        <span class="text-gray-500 dark:text-gray-400">Conditions générales :</span>
                                        <p class="text-gray-900 dark:text-white mt-1">{{ $document->metadata['conditions_generales'] }}</p>
                                    </div>
                                @endif
                            </div>

                            @if(isset($document->metadata['lignes']) && count($document->metadata['lignes']) > 0)
                                <div class="mt-4">
                                    <h4 class="text-sm font-medium text-gray-900 dark:text-white mb-2">Lignes du devis</h4>
                                    <div class="overflow-x-auto">
                                        <table class="w-full text-sm">
                                            <thead>
                                                <tr class="border-b border-gray-200 dark:border-gray-700">
                                                    <th class="text-left py-2 text-gray-500 dark:text-gray-400">Libellé</th>
                                                    <th class="text-right py-2 text-gray-500 dark:text-gray-400">Qté</th>
                                                    <th class="text-right py-2 text-gray-500 dark:text-gray-400">Prix unitaire (FCFA)</th>
                                                    <th class="text-right py-2 text-gray-500 dark:text-gray-400">Total</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($document->metadata['lignes'] as $ligne)
                                                    @php
                                                        $total = ($ligne['quantite'] ?? 0) * ($ligne['prix_unitaire'] ?? 0);
                                                    @endphp
                                                    <tr class="border-b border-gray-100 dark:border-gray-700">
                                                        <td class="py-2 text-gray-900 dark:text-white">{{ $ligne['libelle'] ?? '' }}</td>
                                                        <td class="py-2 text-right text-gray-900 dark:text-white">{{ $ligne['quantite'] ?? 0 }}</td>
                                                        <td class="py-2 text-right text-gray-900 dark:text-white">{{ number_format($ligne['prix_unitaire'] ?? 0, 0, ',', ' ') }}</td>
                                                        <td class="py-2 text-right text-gray-900 dark:text-white">{{ number_format($total, 0, ',', ' ') }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endif
                </div>
            </div>

            <!-- Interface de renvoi (uniquement si le devis n'est pas encore accepté) -->
            @if(! $document->isSigned())
                <div class="border-t border-gray-200 dark:border-gray-700 pt-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Acceptation du devis</h3>

                    <form action="{{ route('client.documents.return', $document) }}" method="POST" id="returnForm">
                        @csrf

                        <!-- Checkbox d'attestation -->
                        <div class="mb-6">
                            <label class="flex items-start gap-3 cursor-pointer">
                                <input type="checkbox" name="attestation" value="1"
                                       id="attestationCheckbox"
                                       class="mt-1 h-4 w-4 rounded border-gray-300 dark:border-gray-600 text-orange-500 focus:ring-orange-500 dark:bg-gray-700 dark:checked:bg-orange-500">
                                <span class="text-sm text-gray-700 dark:text-gray-300">
                                    J'atteste avoir pris connaissance du devis ci-dessus et j'accepte
                                    les termes de ce devis.
                                </span>
                            </label>
                            @error('attestation')
                                <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Bouton d'action -->
                        <div class="flex gap-3">
                            <button type="submit" id="returnBtn"
                                    class="px-6 py-2 bg-orange-500 hover:bg-orange-600 text-white rounded-lg text-sm font-medium transition flex items-center gap-2"
                                    disabled>
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                                </svg>
                                Renvoyer
                            </button>
                        </div>
                    </form>
                </div>
            @else
                <div class="border-t border-gray-200 dark:border-gray-700 pt-6">
                    <div class="flex items-center gap-3 p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg">
                        <i data-lucide="check-circle" class="w-5 h-5 text-green-600 dark:text-green-400"></i>
                        <span class="text-green-800 dark:text-green-300 font-medium">
                            Ce devis a été accepté le {{ $document->signed_at?->format('d/m/Y H:i') ?? 'N/A' }}.
                        </span>
                    </div>
                </div>
            @endif
        </div>
    </x-blade-components::layout.container>
@endsection

@push('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        const form = document.getElementById('returnForm');
        const returnBtn = document.getElementById('returnBtn');
        const attestationCheckbox = document.getElementById('attestationCheckbox');

        // Mettre à jour l'état du bouton
        function updateButton() {
            returnBtn.disabled = !attestationCheckbox.checked;
        }

        attestationCheckbox.addEventListener('change', updateButton);

        // Soumission du formulaire
        form.addEventListener('submit', function(e) {
            if (!attestationCheckbox.checked) {
                e.preventDefault();
                alert('Veuillez cocher la case d\'attestation pour renvoyer le devis.');
                return;
            }

            if (!confirm('Êtes-vous sûr de vouloir renvoyer ce devis à l\'artisan ? Il sera marqué comme accepté et l\'artisan pourra l\'exporter.')) {
                e.preventDefault();
            }
        });
    });
</script>
@endpush