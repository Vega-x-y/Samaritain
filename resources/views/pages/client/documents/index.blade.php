@extends('layouts.base')

@section('title', 'Mes documents')

@section('content')
    <x-ui.user-dashboard-nav />
    <x-blade-components::layout.container>
        <div class="container mx-auto px-4 py-8">
            <!-- En-tête -->
            <div class="mb-8">
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Mes documents</h1>
                <p class="text-gray-500 dark:text-gray-400 mt-1 text-sm">
                    Consultez et acceptez les documents partagés par votre artisan.
                </p>
            </div>

            <!-- Liste des documents -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                @forelse($documents as $document)
                    <div class="flex items-start justify-between p-4 bg-gray-50 dark:bg-gray-800/50 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700/50 transition mb-3 last:mb-0">
                        <div class="flex items-start flex-1">
                            <div class="mr-3 mt-0.5">
                                <i data-lucide="{{ $document->type_icon }}" class="w-8 h-8
                                    {{ $document->type === 'devis' ? 'text-green-500 dark:text-green-400' : '' }}
                                    {{ $document->type === 'facture' ? 'text-blue-500 dark:text-blue-400' : '' }}
                                    {{ $document->type === 'compte_rendu' ? 'text-orange-500 dark:text-orange-400' : '' }}
                                "></i>
                            </div>
                            <div class="flex-1">
                                <a href="{{ route('client.documents.show', $document) }}"
                                   class="text-sm font-medium text-gray-900 dark:text-gray-100 hover:text-blue-600 dark:hover:text-blue-400">
                                    {{ $document->nom }}
                                </a>
                                <div class="flex items-center gap-3 mt-1 flex-wrap">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium
                                        {{ $document->type === 'devis' ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300' : '' }}
                                        {{ $document->type === 'facture' ? 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300' : '' }}
                                        {{ $document->type === 'compte_rendu' ? 'bg-orange-100 text-orange-800 dark:bg-orange-900/30 dark:text-orange-300' : '' }}
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
                                </div>
                            </div>
                        </div>

                        @if($document->isDevis() && ! $document->isSigned())
                            <a href="{{ route('client.documents.show', $document) }}"
                               class="ml-4 inline-flex items-center px-3 py-1.5 bg-orange-500 hover:bg-orange-600 text-white text-xs rounded-lg transition">
                                <i data-lucide="eye" class="w-3.5 h-3.5 mr-1"></i>
                                Consulter
                            </a>
                        @elseif($document->isDevis() && $document->isSigned())
                            <span class="ml-4 inline-flex items-center px-3 py-1.5 bg-green-600 text-white text-xs rounded-lg">
                                <i data-lucide="check" class="w-3.5 h-3.5 mr-1"></i>
                                Accepté
                            </span>
                        @else
                            <a href="{{ route('client.documents.show', $document) }}"
                               class="ml-4 inline-flex items-center px-3 py-1.5 bg-blue-500 hover:bg-blue-600 text-white text-xs rounded-lg transition">
                                <i data-lucide="eye" class="w-3.5 h-3.5 mr-1"></i>
                                Consulter
                            </a>
                        @endif
                    </div>
                @empty
                    <div class="text-center py-8 text-gray-500 dark:text-gray-400">
                        <i data-lucide="file-text" class="w-12 h-12 mx-auto mb-4 text-gray-400 dark:text-gray-500"></i>
                        <p>Aucun document partagé pour le moment.</p>
                    </div>
                @endforelse
            </div>

            @if($documents->hasPages())
                <div class="mt-6">
                    {{ $documents->links() }}
                </div>
            @endif
        </div>
    </x-blade-components::layout.container>
@endsection
