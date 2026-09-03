@extends('layouts.base')

@section('title', 'Mes chantiers')

@section('content')
    <x-ui.user-dashboard-nav />
    <x-blade-components::layout.container>
        <div class="container mx-auto px-4 py-8">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-8">
                <div>
                    <h1 class="text-xl md:text-2xl font-bold text-gray-900 dark:text-white">Mes <span class="text-orange-500">chantiers</span></h1>
                    <p class="text-gray-500 dark:text-gray-400 mt-1 text-sm">Suivez l'avancement de vos travaux</p>
                </div>
                <a href="{{ route('client.dashboard') }}" class="px-4 py-2 rounded-full text-sm font-medium border border-gray-300 text-gray-600 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-400 dark:hover:bg-gray-800 transition">
                    ← Retour au dashboard
                </a>
            </div>

            <!-- Statistiques -->
            <div class="grid grid-cols-2 md:grid-cols-5 gap-3 mb-6">
                <div class="bg-sidebar dark:bg-gray-800 rounded-xl p-3 border border-accent dark:border-gray-700 shadow-sm flex items-center gap-3">
                    <div class="w-11 h-11 bg-blue-50 dark:bg-blue-900/30 rounded-full flex items-center justify-center text-blue-600 dark:text-blue-400"><i data-lucide="clipboard-list" class="w-5 h-5" aria-hidden="true"></i></div>
                    <div>
                        <div class="text-xl font-bold text-gray-900 dark:text-white">{{ $stats['total_chantiers'] }}</div>
                        <div class="text-xs text-gray-500 dark:text-gray-400">Total</div>
                    </div>
                </div>
                <div class="bg-sidebar dark:bg-gray-800 rounded-xl p-3 border border-accent dark:border-gray-700 shadow-sm flex items-center gap-3">
                    <div class="w-11 h-11 bg-orange-50 dark:bg-orange-900/30 rounded-full flex items-center justify-center text-orange-600 dark:text-orange-400"><i data-lucide="zap" class="w-5 h-5" aria-hidden="true"></i></div>
                    <div>
                        <div class="text-xl font-bold text-orange-600 dark:text-orange-400">{{ $stats['chantiers_en_cours'] }}</div>
                        <div class="text-xs text-gray-500 dark:text-gray-400">En cours</div>
                    </div>
                </div>
                <div class="bg-sidebar dark:bg-gray-800 rounded-xl p-3 border border-accent dark:border-gray-700 shadow-sm flex items-center gap-3">
                    <div class="w-11 h-11 bg-emerald-50 dark:bg-emerald-900/30 rounded-full flex items-center justify-center text-emerald-600 dark:text-emerald-400"><i data-lucide="circle-check" class="w-5 h-5" aria-hidden="true"></i></div>
                    <div>
                        <div class="text-xl font-bold text-emerald-600 dark:text-emerald-400">{{ $stats['chantiers_termines'] }}</div>
                        <div class="text-xs text-gray-500 dark:text-gray-400">Terminés</div>
                    </div>
                </div>
                <div class="bg-sidebar dark:bg-gray-800 rounded-xl p-3 border border-accent dark:border-gray-700 shadow-sm flex items-center gap-3">
                    <div class="w-11 h-11 bg-amber-50 dark:bg-amber-900/30 rounded-full flex items-center justify-center text-amber-600 dark:text-amber-400"><i data-lucide="hourglass" class="w-5 h-5" aria-hidden="true"></i></div>
                    <div>
                        <div class="text-xl font-bold text-amber-600 dark:text-amber-400">{{ $stats['chantiers_en_attente'] }}</div>
                        <div class="text-xs text-gray-500 dark:text-gray-400">En attente</div>
                    </div>
                </div>
                <div class="bg-sidebar dark:bg-gray-800 rounded-xl p-3 border border-accent dark:border-gray-700 shadow-sm flex items-center gap-3">
                    <div class="w-11 h-11 bg-red-50 dark:bg-red-900/30 rounded-full flex items-center justify-center text-red-600 dark:text-red-400"><i data-lucide="circle-stop" class="w-5 h-5" aria-hidden="true"></i></div>
                    <div>
                        <div class="text-xl font-bold text-red-600 dark:text-red-400">{{ $stats['chantiers_en_arret'] }}</div>
                        <div class="text-xs text-gray-500 dark:text-gray-400">En arrêt</div>
                    </div>
                </div>
            </div>

            <!-- Filtres par statut -->
            <div class="flex flex-wrap gap-2 items-center mb-6">
                <a href="{{ route('client.chantiers.index') }}"
                    class="px-4 py-1.5 rounded-full text-sm font-medium border transition
                        {{ !request('statut') ? 'bg-gray-900 text-white border-gray-900 dark:bg-white dark:text-gray-900' : 'bg-white dark:bg-gray-800 text-gray-500 dark:text-gray-400 border-gray-200 dark:border-gray-700 hover:border-orange-500' }}">
                    <i data-lucide="chart-column" class="inline-block w-4 h-4 mr-1" aria-hidden="true"></i> Tous
                </a>
                <a href="{{ route('client.chantiers.index', ['statut' => 'en_cours']) }}"
                    class="px-4 py-1.5 rounded-full text-sm font-medium border transition
                        {{ request('statut') === 'en_cours' ? 'bg-orange-500 text-white border-orange-500' : 'bg-white dark:bg-gray-800 text-gray-500 dark:text-gray-400 border-gray-200 dark:border-gray-700 hover:border-orange-500' }}">
                    <i data-lucide="zap" class="inline-block w-4 h-4 mr-1" aria-hidden="true"></i> En cours
                </a>
                <a href="{{ route('client.chantiers.index', ['statut' => 'termine']) }}"
                    class="px-4 py-1.5 rounded-full text-sm font-medium border transition
                        {{ request('statut') === 'termine' ? 'bg-emerald-500 text-white border-emerald-500' : 'bg-white dark:bg-gray-800 text-gray-500 dark:text-gray-400 border-gray-200 dark:border-gray-700 hover:border-orange-500' }}">
                    <i data-lucide="circle-check" class="inline-block w-4 h-4 mr-1" aria-hidden="true"></i> Terminés
                </a>
                <a href="{{ route('client.chantiers.index', ['statut' => 'attente']) }}"
                    class="px-4 py-1.5 rounded-full text-sm font-medium border transition
                        {{ request('statut') === 'attente' ? 'bg-amber-500 text-white border-amber-500' : 'bg-white dark:bg-gray-800 text-gray-500 dark:text-gray-400 border-gray-200 dark:border-gray-700 hover:border-orange-500' }}">
                    <i data-lucide="hourglass" class="inline-block w-4 h-4 mr-1" aria-hidden="true"></i> En attente
                </a>
                <a href="{{ route('client.chantiers.index', ['statut' => 'arret']) }}"
                    class="px-4 py-1.5 rounded-full text-sm font-medium border transition
                        {{ request('statut') === 'arret' ? 'bg-red-500 text-white border-red-500' : 'bg-white dark:bg-gray-800 text-gray-500 dark:text-gray-400 border-gray-200 dark:border-gray-700 hover:border-orange-500' }}">
                    <i data-lucide="circle-stop" class="inline-block w-4 h-4 mr-1" aria-hidden="true"></i> En arrêt
                </a>
                <span class="text-xs text-gray-400 dark:text-gray-500 ml-2">{{ $chantiers->total() }} chantier(s)</span>
            </div>

            <!-- Liste des chantiers -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                @forelse ($chantiers as $chantier)
                    <div class="bg-sidebar dark:bg-gray-800 rounded-xl p-5 shadow-sm border border-accent dark:border-gray-700 transition hover:-translate-y-1 hover:shadow-md hover:border-orange-300 dark:hover:border-orange-700">
                        <div class="flex justify-between items-start">
                            <div>
                                <div class="font-semibold text-lg text-gray-900 dark:text-white">{{ $chantier->nom }}</div>
                                <div class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                                    {{ $chantier->artisan?->business_name ?? 'Artisan non assigné' }}
                                </div>
                            </div>
                            <span class="text-xs font-semibold uppercase px-2 py-0.5 rounded-full"
                                @class([
                                    'bg-blue-50 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300' => $chantier->type === 'plomberie',
                                    'bg-orange-50 text-orange-700 dark:bg-orange-900/30 dark:text-orange-300' => $chantier->type === 'electricite',
                                    'bg-pink-50 text-pink-700 dark:bg-pink-900/30 dark:text-pink-300' => $chantier->type === 'peinture',
                                    'bg-emerald-50 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300' => $chantier->type === 'maconnerie',
                                    'bg-lime-50 text-lime-700 dark:bg-lime-900/30 dark:text-lime-300' => $chantier->type === 'menuiserie',
                                ])>
                                {{ ucfirst($chantier->type) }}
                            </span>
                        </div>

                        <div class="flex flex-wrap gap-4 text-sm text-gray-500 dark:text-gray-400 mt-3">
                            <span class="inline-flex items-center gap-1.5"><i data-lucide="calendar-days" class="w-4 h-4" aria-hidden="true"></i> {{ $chantier->created_at->format('d/m/Y') }}</span>
                            <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full text-xs font-semibold {{ $chantier->statut->colorClass() }}">
                                <span class="w-1.5 h-1.5 rounded-full {{ $chantier->statut->dotColorClass() }}"></span>
                                {{ $chantier->statut->label() }}
                            </span>
                        </div>

                        <div class="flex justify-between items-center mt-4 pt-3 border-t border-gray-100 dark:border-gray-700">
                            <span class="font-bold text-lg text-gray-900 dark:text-white">{{ number_format($chantier->budget ?? 0, 0, ',', ' ') }} FCFA</span>
                            <span class="text-xs text-gray-500 dark:text-gray-400 bg-gray-50 dark:bg-gray-700 px-3 py-0.5 rounded-full">{{ $chantier->progress }}%</span>
                        </div>
                        <div class="mt-2">
                            <div class="relative w-full h-2 bg-gray-200 dark:bg-gray-700 rounded-full overflow-hidden">
                                <div class="h-full bg-orange-500 rounded-full transition-all" style="width: {{ $chantier->progress }}%"></div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-span-full text-center py-16 text-gray-500 dark:text-gray-400">
                        <i data-lucide="clipboard-list" class="mx-auto w-12 h-12 mb-2" aria-hidden="true"></i>
                        <p>Aucun chantier pour le moment.</p>
                        <p class="text-sm mt-1">Vos chantiers apparaîtront ici dès qu'un artisan en créera un pour vous.</p>
                    </div>
                @endforelse
            </div>

            @if($chantiers->hasPages())
                <div class="mt-6">
                    {{ $chantiers->links() }}
                </div>
            @endif
        </div>
    </x-blade-components::layout.container>
@endsection