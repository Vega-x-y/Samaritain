@extends('layouts.artisan')

@section('title', 'Finances')

@section('breadcrumbs')
    <nav class="flex items-center gap-2 text-sm text-muted-foreground">
        <i data-lucide="wallet" class="w-4 h-4"></i>
        <span>Finances</span>
    </nav>
@endsection

@section('content')
    <div class="space-y-6">
        <!-- En-tête -->
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-foreground">Finances</h1>
                <p class="text-sm text-muted-foreground mt-1">Gestion financière de vos chantiers</p>
            </div>
            <a href="{{ route('artisan.chantiers.index') }}" 
               class="inline-flex items-center px-4 py-2 bg-orange-500 hover:bg-orange-600 text-white rounded-lg transition-all duration-200 hover:scale-105 active:scale-95">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Retour aux chantiers
            </a>
        </div>

        <!-- Filtres -->
        <div class="bg-card rounded-lg shadow-sm border border-border p-6 transition-all duration-300 hover:shadow-lg">
            <form method="GET" action="{{ route('artisan.finances.index') }}" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-foreground mb-2">Chantier</label>
                    <select name="chantier_id" class="w-full rounded-lg border-border focus:border-orange-500 focus:ring-orange-500 bg-background transition-all duration-200 focus:shadow-md">
                        <option value="">Tous les chantiers</option>
                        @foreach($chantiersList as $chantier)
                            <option value="{{ $chantier->id }}" {{ request('chantier_id') == $chantier->id ? 'selected' : '' }}>
                                {{ $chantier->nom }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="flex items-end">
                    <button type="submit" class="w-full px-4 py-2 bg-orange-500 hover:bg-orange-600 text-white rounded-lg transition-all duration-200 hover:scale-105 active:scale-95">
                        Filtrer
                    </button>
                </div>
                <div class="flex items-end">
                    <a href="{{ route('artisan.finances.index') }}" 
                       class="w-full px-4 py-2 bg-orange-500 hover:bg-orange-600 text-white rounded-lg transition-all duration-200 hover:scale-105 active:scale-95 text-center">
                        Réinitialiser
                    </a>
                </div>
            </form>
        </div>

        <!-- Liste des chantiers -->
        <div class="bg-card rounded-lg shadow-sm border border-border overflow-hidden transition-all duration-300 hover:shadow-lg">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-orange-200">
                    <thead class="bg-orange-50 dark:bg-orange-900/20">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase">Chantier</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground uppercase">Client</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-muted-foreground uppercase">CA</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-muted-foreground uppercase">Dépenses</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-muted-foreground uppercase">Rentabilité</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-muted-foreground uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-card divide-y divide-border">
                        @forelse($chantiers as $chantier)
                            @php
                                $rentabilite = $chantier->calculerRentabilite();
                                $totalCA = $chantier->total_ca;
                                $totalDepenses = $chantier->total_depenses;
                            @endphp
                            <tr class="hover:bg-muted/30 transition-colors duration-150">
                                <td class="px-6 py-4">
                                    <div class="text-sm font-medium text-foreground">{{ $chantier->nom }}</div>
                                    <div class="text-sm text-muted-foreground">{{ $chantier->type }}</div>
                                </td>
                                <td class="px-6 py-4 text-sm text-foreground">
                                    {{ $chantier->client?->name ?? 'Non assigné' }}
                                </td>
                                <td class="px-6 py-4 text-right text-sm font-medium text-green-600">
                                    {{ number_format($totalCA, 2, ',', ' ') }} FCFA
                                </td>
                                <td class="px-6 py-4 text-right text-sm font-medium text-red-600">
                                    {{ number_format($totalDepenses, 2, ',', ' ') }} FCFA
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $rentabilite >= 0 ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400' : 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400' }}">
                                        {{ number_format($rentabilite, 2, ',', ' ') }} FCFA
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <a href="{{ route('artisan.finances.show', $chantier) }}" 
                                       class="inline-flex items-center px-3 py-1.5 bg-orange-500 hover:bg-orange-600 text-white text-sm rounded-lg transition-all duration-200 hover:scale-105 active:scale-95">
                                        Détails
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center text-muted-foreground">
                                    <svg class="mx-auto h-12 w-12 text-muted-foreground/60 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                    </svg>
                                    Aucun chantier trouvé
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($chantiers->hasPages())
                <div class="px-6 py-4 border-t border-border">
                    {{ $chantiers->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection