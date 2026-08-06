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

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-8">
                <div class="bg-sidebar dark:bg-gray-800 rounded-xl shadow-sm p-5 border border-accent dark:border-gray-700">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-500 dark:text-gray-400 text-sm font-medium">Chantiers</p>
                            <p class="text-3xl font-bold text-gray-900 dark:text-white mt-1">{{ $stats['total_chantiers'] }}</p>
                        </div>
                        <div class="w-12 h-12 bg-blue-100 dark:bg-blue-900/30 rounded-xl flex items-center justify-center">
                            <i data-lucide="hammer" class="w-6 h-6 text-blue-600 dark:text-blue-400"></i>
                        </div>
                    </div>
                </div>
                <div class="bg-sidebar dark:bg-gray-800 rounded-xl shadow-sm p-5 border border-accent dark:border-gray-700">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-500 dark:text-gray-400 text-sm font-medium">En cours</p>
                            <p class="text-3xl font-bold text-amber-600 dark:text-amber-400 mt-1">{{ $stats['chantiers_en_cours'] }}</p>
                        </div>
                        <div class="w-12 h-12 bg-amber-100 dark:bg-amber-900/30 rounded-xl flex items-center justify-center">
                            <i data-lucide="loader" class="w-6 h-6 text-amber-600 dark:text-amber-400"></i>
                        </div>
                    </div>
                </div>
                <div class="bg-sidebar dark:bg-gray-800 rounded-xl shadow-sm p-5 border border-accent dark:border-gray-700">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-500 dark:text-gray-400 text-sm font-medium">Terminés</p>
                            <p class="text-3xl font-bold text-emerald-600 dark:text-emerald-400 mt-1">{{ $stats['chantiers_termines'] }}</p>
                        </div>
                        <div class="w-12 h-12 bg-emerald-100 dark:bg-emerald-900/30 rounded-xl flex items-center justify-center">
                            <i data-lucide="check-circle" class="w-6 h-6 text-emerald-600 dark:text-emerald-400"></i>
                        </div>
                    </div>
                </div>
                <div class="bg-sidebar dark:bg-gray-800 rounded-xl shadow-sm p-5 border border-accent dark:border-gray-700">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-500 dark:text-gray-400 text-sm font-medium">En attente</p>
                            <p class="text-3xl font-bold text-amber-600 dark:text-amber-400 mt-1">{{ $stats['chantiers_en_attente'] }}</p>
                        </div>
                        <div class="w-12 h-12 bg-amber-100 dark:bg-amber-900/30 rounded-xl flex items-center justify-center">
                            <i data-lucide="hourglass" class="w-6 h-6 text-amber-600 dark:text-amber-400"></i>
                        </div>
                    </div>
                </div>
                <div class="bg-sidebar dark:bg-gray-800 rounded-xl shadow-sm p-5 border border-accent dark:border-gray-700">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-500 dark:text-gray-400 text-sm font-medium">En arrêt</p>
                            <p class="text-3xl font-bold text-red-600 dark:text-red-400 mt-1">{{ $stats['chantiers_en_arret'] }}</p>
                        </div>
                        <div class="w-12 h-12 bg-red-100 dark:bg-red-900/30 rounded-xl flex items-center justify-center">
                            <i data-lucide="octagon-alert" class="w-6 h-6 text-red-600 dark:text-red-400"></i>
                        </div>
                    </div>
                </div>
                <div class="bg-sidebar dark:bg-gray-800 rounded-xl shadow-sm p-5 border border-accent dark:border-gray-700">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-500 dark:text-gray-400 text-sm font-medium">Documents</p>
                            <p class="text-3xl font-bold text-purple-600 dark:text-purple-400 mt-1">{{ $stats['total_documents'] }}</p>
                        </div>
                        <div class="w-12 h-12 bg-purple-100 dark:bg-purple-900/30 rounded-xl flex items-center justify-center">
                            <i data-lucide="file-text" class="w-6 h-6 text-purple-600 dark:text-purple-400"></i>
                        </div>
                    </div>
                </div>
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
            </div>
        </div>
    </x-blade-components::layout.container>
@endsection