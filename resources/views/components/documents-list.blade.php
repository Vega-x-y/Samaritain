@props(['documents', 'showActions' => true, 'clients' => []])

<div class="documents-list space-y-3">
    @forelse($documents as $document)
        <div class="flex items-start justify-between p-4 bg-gray-50 dark:bg-gray-800/50 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700/50 transition" x-data="{ showSend: false }">
            <div class="flex items-start flex-1">
                <div class="mr-3 mt-0.5">
                    <i data-lucide="{{ $document->type_icon }}" class="w-8 h-8
                        {{ $document->type === 'devis' ? 'text-green-500 dark:text-green-400' : '' }}
                        {{ $document->type === 'facture' ? 'text-blue-500 dark:text-blue-400' : '' }}
                        {{ $document->type === 'attestation' ? 'text-red-500 dark:text-red-400' : '' }}
                        {{ $document->type === 'compte_rendu' ? 'text-orange-500 dark:text-orange-400' : '' }}
                        {{ $document->type === 'image' ? 'text-blue-500 dark:text-blue-400' : '' }}
                        {{ $document->type === 'pdf' ? 'text-red-500 dark:text-red-400' : '' }}
                        {{ in_array($document->type, ['document']) ? 'text-gray-500 dark:text-gray-400' : '' }}
                    "></i>
                </div>

                <div class="flex-1">
                    <a href="{{ $document->url }}" target="_blank" class="text-sm font-medium text-gray-900 dark:text-gray-100 hover:text-blue-600 dark:hover:text-blue-400">
                        {{ $document->nom }}
                    </a>
                    <div class="flex items-center gap-3 mt-1 flex-wrap">
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium
                            {{ $document->type === 'devis' ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300' : '' }}
                            {{ $document->type === 'facture' ? 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300' : '' }}
                            {{ $document->type === 'attestation' ? 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300' : '' }}
                            {{ $document->type === 'compte_rendu' ? 'bg-orange-100 text-orange-800 dark:bg-orange-900/30 dark:text-orange-300' : '' }}
                            {{ $document->type === 'image' ? 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300' : '' }}
                            {{ $document->type === 'pdf' ? 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300' : '' }}
                            {{ in_array($document->type, ['document']) ? 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300' : '' }}
                        ">
                            {{ $document->type_label }}
                        </span>

                        @if($document->isDevis())
                            @php
                                $statusColors = [
                                    'draft' => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
                                    'sent' => 'bg-orange-100 text-orange-800 dark:bg-orange-900/30 dark:text-orange-300',
                                    'signed' => 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300',
                                ];
                            @endphp
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $statusColors[$document->status] ?? $statusColors['draft'] }}">
                                {{ $document->status_label }}
                            </span>
                        @endif

                        <span class="text-xs text-gray-500 dark:text-gray-400">{{ $document->formatted_size }}</span>
                        <span class="text-xs text-gray-500 dark:text-gray-400">{{ $document->created_at->format('d/m/Y') }}</span>
                        @if($document->client)
                            <span class="text-xs text-gray-500 dark:text-gray-400 font-medium">
                                <i data-lucide="user" class="inline-block w-3.5 h-3.5 mr-1" aria-hidden="true"></i>
                                {{ $document->client->nom }}
                            </span>
                        @endif
                        @if($document->date_modification)
                            <span class="text-xs text-gray-400 dark:text-gray-500" title="Dernière modification">
                                • Modifié le {{ $document->formatted_date_modification }}
                            </span>
                        @endif
                    </div>

                    <!-- Bloc Envoyer vers (affiché au clic) -->
                    <div x-show="showSend" x-transition class="mt-3 pt-3 border-t border-gray-200 dark:border-gray-700">
                        <form action="{{ route('artisan.documents.send-to-client', $document) }}" method="POST" class="flex items-center gap-2">
                            @csrf
                            <select name="client_id" required class="text-sm rounded-lg border-gray-300 dark:border-gray-600 focus:border-orange-500 focus:ring-orange-500 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100">
                                <option value="">Choisir un client...</option>
                                @foreach($clients as $c)
                                    <option value="{{ $c->id }}" {{ $document->client_id == $c->id ? 'selected' : '' }}>{{ $c->nom }}</option>
                                @endforeach
                            </select>
                            <button type="submit" class="px-3 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs rounded-lg transition">
                                Envoyer
                            </button>
                            <button type="button" @click="showSend = false" class="px-3 py-1.5 bg-gray-300 dark:bg-gray-600 hover:bg-gray-400 dark:hover:bg-gray-500 text-gray-700 dark:text-gray-300 text-xs rounded-lg transition">
                                Annuler
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            @if($showActions)
                <div class="flex items-center gap-1.5 ml-4 flex-shrink-0">
                    @if($document->isDevis() && ! $document->canExport())
                        <button type="button"
                                class="inline-flex items-center px-2.5 py-1.5 bg-gray-400 dark:bg-gray-600 text-gray-300 text-xs rounded-lg cursor-not-allowed"
                                title="Le devis doit être accepté par le client avant d'être consultable">
                            <i data-lucide="eye" class="w-3.5 h-3.5"></i>
                        </button>
                    @else
                        <a href="{{ $document->url }}" target="_blank"
                           class="inline-flex items-center px-2.5 py-1.5 bg-blue-600 hover:bg-blue-700 text-white text-xs rounded-lg transition"
                           title="Voir">
                            <i data-lucide="eye" class="w-3.5 h-3.5"></i>
                        </a>
                    @endif

                    @if($document->canExport())
                        <a href="{{ route('artisan.documents.export-pdf', $document) }}"
                           class="inline-flex items-center px-2.5 py-1.5 bg-green-600 hover:bg-green-700 text-white text-xs rounded-lg transition"
                           title="Exporter PDF">
                            <i data-lucide="download" class="w-3.5 h-3.5"></i>
                        </a>
                    @else
                        <button type="button"
                               class="inline-flex items-center px-2.5 py-1.5 bg-gray-400 dark:bg-gray-600 text-gray-300 text-xs rounded-lg cursor-not-allowed"
                               title="Ce devis doit être accepté par le client avant d'être exporté">
                            <i data-lucide="download" class="w-3.5 h-3.5"></i>
                        </button>
                    @endif

                    @if($document->isDevis() && ! $document->isSent())
                        <button @click="showSend = !showSend"
                               class="inline-flex items-center px-2.5 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs rounded-lg transition"
                               title="Envoyer vers un client">
                            <i data-lucide="send" class="w-3.5 h-3.5"></i>
                        </button>
                    @elseif($document->isDevis() && $document->isSent() && ! $document->isSigned())
                        <span class="inline-flex items-center px-2.5 py-1.5 bg-gray-400 dark:bg-gray-600 text-gray-300 text-xs rounded-lg"
                              title="En attente d'acceptation client">
                            <i data-lucide="clock" class="w-3.5 h-3.5"></i>
                        </span>
                    @elseif($document->isDevis() && $document->isSigned())
                        <span class="inline-flex items-center px-2.5 py-1.5 bg-green-600 text-white text-xs rounded-lg"
                              title="Devis accepté">
                            <i data-lucide="check" class="w-3.5 h-3.5"></i>
                        </span>
                    @else
                        <button @click="showSend = !showSend"
                               class="inline-flex items-center px-2.5 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs rounded-lg transition"
                               title="Envoyer vers un client">
                            <i data-lucide="send" class="w-3.5 h-3.5"></i>
                        </button>
                    @endif

                    <a href="{{ route('artisan.documents.edit', $document) }}"
                       class="inline-flex items-center px-2.5 py-1.5 bg-orange-600 hover:bg-orange-700 text-white text-xs rounded-lg transition"
                       title="Modifier">
                        <i data-lucide="pencil" class="w-3.5 h-3.5"></i>
                    </a>
                    <form action="{{ route('artisan.documents.destroy', $document) }}" method="POST"
                          class="inline" onsubmit="return confirm('Supprimer ce document ?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="inline-flex items-center px-2.5 py-1.5 bg-red-600 hover:bg-red-700 text-white text-xs rounded-lg transition"
                                title="Supprimer">
                            <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                        </button>
                    </form>
                </div>
            @endif
        </div>
    @empty
        <div class="text-center py-8 text-gray-500 dark:text-gray-400">
            <svg class="mx-auto h-12 w-12 text-gray-400 dark:text-gray-500 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            <p>Aucun document</p>
        </div>
    @endforelse
</div>
