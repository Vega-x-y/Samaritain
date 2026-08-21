@extends('layouts.base')

@section('title', 'Mon espace client')

@section('content')
    <x-ui.user-dashboard-nav />
    <x-blade-components::layout.container>
        <div class="container mx-auto px-4 py-8">
            <div class="mb-8">
                <h1 class="text-xl md:text-2xl font-bold text-gray-900 dark:text-white">Mon <span class="text-orange-500">espace client</span></h1>
                <p class="text-gray-500 dark:text-gray-400 mt-1 text-sm">Suivez vos chantiers et documents</p>
            </div>

            @php
                $dashboardCards = [
                    ['label' => 'Chantiers', 'value' => $stats['total_chantiers'], 'icon' => 'hammer', 'text' => 'text-blue-600 dark:text-blue-400', 'iconBackground' => 'bg-blue-100 dark:bg-blue-900/30'],
                    ['label' => 'En cours', 'value' => $stats['chantiers_en_cours'], 'icon' => 'loader', 'text' => 'text-amber-600 dark:text-amber-400', 'iconBackground' => 'bg-amber-100 dark:bg-amber-900/30'],
                    ['label' => 'Terminés', 'value' => $stats['chantiers_termines'], 'icon' => 'check-circle', 'text' => 'text-emerald-600 dark:text-emerald-400', 'iconBackground' => 'bg-emerald-100 dark:bg-emerald-900/30'],
                    ['label' => 'En attente', 'value' => $stats['chantiers_en_attente'], 'icon' => 'hourglass', 'text' => 'text-amber-600 dark:text-amber-400', 'iconBackground' => 'bg-amber-100 dark:bg-amber-900/30'],
                    ['label' => 'En arrêt', 'value' => $stats['chantiers_en_arret'], 'icon' => 'octagon-alert', 'text' => 'text-red-600 dark:text-red-400', 'iconBackground' => 'bg-red-100 dark:bg-red-900/30'],
                    ['label' => 'Documents', 'value' => $stats['total_documents'], 'icon' => 'file-text', 'text' => 'text-purple-600 dark:text-purple-400', 'iconBackground' => 'bg-purple-100 dark:bg-purple-900/30'],
                    ['label' => 'Pass visite', 'value' => $visitPasses->count(), 'icon' => 'ticket', 'text' => 'text-orange-600 dark:text-orange-400', 'iconBackground' => 'bg-orange-100 dark:bg-orange-900/30'],
                ];
            @endphp

            @foreach($dashboardCards as $card)
                <div class="bg-sidebar dark:bg-gray-800 rounded-xl shadow-sm p-5 border border-accent dark:border-gray-700">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-500 dark:text-gray-400 text-sm font-medium">{{ $card['label'] }}</p>
                            <p class="text-3xl font-bold {{ $card['text'] }} mt-1">{{ $card['value'] }}</p>
                        </div>
                        <div class="w-12 h-12 {{ $card['iconBackground'] }} rounded-xl flex items-center justify-center">
                            <i data-lucide="{{ $card['icon'] }}" class="w-6 h-6 {{ $card['text'] }}"></i>
                        </div>
                    </div>
                </div>
            @endforeach
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div class="bg-sidebar dark:bg-gray-800 rounded-xl shadow-sm border border-accent dark:border-gray-700 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex justify-between items-center">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Chantiers récents</h2>
                        <a href="{{ route('client.chantiers.index') }}" class="text-orange-500 hover:text-orange-600 text-sm font-medium">Voir tout →</a>
                    </div>
                    <div class="divide-y divide-gray-100 dark:divide-gray-700">
                        @forelse ($chantiers as $chantier)
                            <div class="px-6 py-4 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition">
                                <div class="flex justify-between items-center">
                                    <div>
                                        <p class="font-medium text-gray-900 dark:text-white">{{ $chantier->nom }}</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ $chantier->artisan?->business_name }} · {{ $chantier->type }}</p>
                                    </div>
                                    <span class="text-xs px-2 py-1 rounded-full {{ $chantier->statut->colorClass() }}">{{ $chantier->statut->label() }}</span>
                                </div>
                            </div>
                        @empty
                            <p class="px-6 py-8 text-center text-gray-500 dark:text-gray-400 text-sm">Aucun chantier pour le moment.</p>
                        @endforelse
                    </div>
                </div>

                <div class="bg-sidebar dark:bg-gray-800 rounded-xl shadow-sm border border-accent dark:border-gray-700 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Documents récents</h2>
                    </div>
                    <div class="divide-y divide-gray-100 dark:divide-gray-700">
                        @forelse ($documents as $document)
                            <div class="px-6 py-4 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition">
                                <div class="flex justify-between items-center">
                                    <div>
                                        <p class="font-medium text-gray-900 dark:text-white">{{ $document->nom }}</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ $document->type_label }} · {{ $document->formatted_size }}</p>
                                    </div>
                                    <a href="{{ $document->url }}" target="_blank" class="text-orange-500 hover:text-orange-600 text-sm font-medium">Voir</a>
                                </div>
                            </div>
                        @empty
                            <p class="px-6 py-8 text-center text-gray-500 dark:text-gray-400 text-sm">Aucun document pour le moment.</p>
                        @endforelse
                    </div>
                </div>

                <div class="bg-sidebar dark:bg-gray-800 rounded-xl shadow-sm border border-accent dark:border-gray-700 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex justify-between items-center">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Pass visite récents</h2>
                        <a href="{{ route('my-visit-passes.index') }}" class="text-orange-500 hover:text-orange-600 text-sm font-medium">Voir tout</a>
                    </div>
                    <div class="divide-y divide-gray-100 dark:divide-gray-700">
                        @forelse ($visitPasses as $visitPass)
                            <div class="px-6 py-4 flex items-center justify-between gap-4">
                                <div class="min-w-0">
                                    <p class="font-medium text-gray-900 dark:text-white truncate">{{ $visitPass->reference }}</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 truncate">{{ $visitPass->visitPassable?->titre ?? $visitPass->visitPassable?->title ?? 'Visite' }}</p>
                                </div>
                                <a href="{{ route('my-visit-passes.show', $visitPass) }}" class="shrink-0 text-orange-500 hover:text-orange-600 text-sm font-medium">Détails</a>
                            </div>
                        @empty
                            <p class="px-6 py-8 text-center text-gray-500 dark:text-gray-400 text-sm">Aucun pass visite pour le moment.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </x-blade-components::layout.container>
@endsection