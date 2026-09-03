@extends('layouts.artisan')

@section('title', 'Centre financier')

@section('breadcrumbs')
    <nav class="flex items-center gap-2 text-sm text-muted-foreground">
        <i data-lucide="pie-chart" class="w-4 h-4"></i>
        <span>Centre financier</span>
    </nav>
@endsection

@section('content')
    @php
        $categorieLabels = [
            'materiaux' => 'Matériaux',
            'main_oeuvre' => "Main d'oeuvre",
            'transport' => 'Transport',
            'autre' => 'Autre',
        ];

        $kpis = [
            ['label' => 'CA Total', 'value' => $totalCA, 'suffix' => 'FCFA', 'border' => 'border-emerald-500', 'text' => 'text-emerald-500'],
            ['label' => 'Dépenses totales', 'value' => $totalDepenses, 'suffix' => 'FCFA', 'border' => 'border-red-500', 'text' => 'text-red-500'],
            ['label' => 'Bénéfice net', 'value' => $beneficeNet, 'suffix' => 'FCFA', 'border' => 'border-emerald-500', 'text' => 'text-emerald-500'],
            ['label' => 'Marge', 'value' => $marge, 'suffix' => '%', 'border' => 'border-blue-500', 'text' => 'text-blue-500'],
            ['label' => 'Acomptes reçus', 'value' => $acomptesRecus, 'suffix' => 'FCFA', 'border' => 'border-primary', 'text' => 'text-primary'],
            ['label' => 'Impayés', 'value' => $impayes, 'suffix' => 'FCFA', 'border' => 'border-red-500', 'text' => 'text-red-500'],
        ];

        
    @endphp

    <div class="space-y-6">
        <!-- ===== HEADER ===== -->
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-foreground">Centre financier</h1>
                <p class="text-sm text-muted-foreground mt-1">Gestion financière de vos chantiers</p>
            </div>
            <div class="flex items-center gap-3">
                <!-- Cloche notifications -->
                <x-btn href="{{ route('notifications.api') }}" size="icon" style="ghost"
                       class="relative rounded-full bg-card border border-border text-muted-foreground hover:text-foreground hover:border-primary transition-all duration-200 hover:rotate-[-5deg]"
                       aria-label="Notifications">
                    <i data-lucide="bell" class="w-5 h-5"></i>
                </x-btn>
                <!-- Nouveau projet -->
                <x-btn href="{{ route('artisan.chantiers.create') }}" size="lg">
                    <x-slot:prefix><i data-lucide="plus" class="w-4 h-4"></i></x-slot:prefix>
                    Nouveau projet
                </x-btn>
            </div>
        </div>

        <!-- ===== SOUS-HEADER ===== -->
        <x-card>
            <div class="flex flex-wrap items-center justify-between gap-4 px-5 py-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-primary/10 dark:bg-primary/20 flex items-center justify-center transition-all duration-300 hover:scale-110">
                        <i data-lucide="pie-chart" class="w-5 h-5 text-primary"></i>
                    </div>
                    <div>
                        <p class="font-semibold text-foreground">Centre financier</p>
                        <p class="text-xs text-muted-foreground">Vue d'ensemble de vos finances</p>
                    </div>
                </div>
                <x-btn type="button" style="secondary"
                       class="rounded-lg bg-zinc-800 text-white hover:bg-zinc-700 active:scale-95 hover:shadow-lg">
                    <i data-lucide="download" class="w-4 h-4"></i>
                    Exporter le bilan
                </x-btn>
            </div>
        </x-card>

        <!-- ===== CARTES KPI ===== -->
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4">
            @foreach ($kpis as $kpi)
                <x-card class="px-3 py-2">
                    <p class="text-[11px] font-semibold uppercase tracking-wider text-muted-foreground">
                        {{ $kpi['label'] }}
                    </p>
                    <p class="mt-3 text-xl sm:text-2xl font-bold text-foreground truncate">
                        {{ number_format($kpi['value'], 0, ',', ' ') }}
                        <span class="text-xs sm:text-sm font-medium {{ $kpi['text'] }}">
                            {{ $kpi['suffix'] }}
                        </span>
                    </p>
                </x-card>
            @endforeach
        </div>
        <!-- Barre de recherche -->
        <div class="mb-4">
            @include('components.artisan.search-bar', ['placeholder' => 'Rechercher une transaction'])
        </div>

        <!-- ===== Enregistrement rapide d'une dépense ===== -->
        <x-card class="rounded-2xl shadow-sm p-6 transition-all duration-300">
            <div class="flex items-center justify-between gap-4 mb-4">
                <div>
                    <h3 class="text-lg font-semibold text-foreground">Nouvelle dépense</h3>
                    <p class="text-sm text-muted-foreground">Enregistrez rapidement une sortie d'argent.</p>
                </div>
            </div>

            <form id="depense-quick-form" method="POST" action="{{ route('artisan.finances.store-depense', $chantiersList->first()?->id ?? 0) }}" enctype="multipart/form-data" class="grid grid-cols-1 md:grid-cols-5 gap-4 items-end">
                @csrf
                <div class="md:col-span-2">
                    <x-form.select name="chantier_id" label="Chantier" placeholder="Sélectionner un chantier" :options="$chantiersList" optionValue="id" optionLabel="nom" required />
                </div>
                <div>
                    <x-form.select name="categorie" label="Type" :options="$categorieLabels" required />
                </div>
                <div>
                    <x-form.input type="number" step="0.01" name="montant" label="Montant" required placeholder="Ex: 15000" />
                </div>
                <div>
                    <x-form.input type="date" name="date" label="Date" :value="now()->format('Y-m-d')" />
                </div>
                <div class="md:col-span-5">
                    <x-btn type="submit" class="rounded-lg hover:scale-105 active:scale-95">
                        <i data-lucide="save" class="w-4 h-4"></i>
                        Enregistrer la dépense
                    </x-btn>
                </div>
            </form>
        </x-card>

        <script>
            (function () {
                const form = document.getElementById('depense-quick-form');
                const select = document.getElementById('chantier_id');
                if (!form || !select) {
                    return;
                }

                const updateAction = () => {
                    const id = select.value || '0';
                    form.action = `{{ url('/artisan/finances') }}/${id}/depenses`;
                };

                select.addEventListener('change', updateAction);
                updateAction();
            })();
        </script>

        <!-- ===== BLocs revenus / dépenses ===== -->

        <!-- ===== BLocs revenus / dépenses ===== -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Répartition des revenus -->
            <x-card class="rounded-2xl shadow-sm p-6 transition-all duration-300">
                <h3 class="text-sm font-semibold uppercase tracking-wider text-muted-foreground mb-5">
                    Répartition des revenus
                </h3>
                <div class="space-y-4">
                    @forelse ($repartitionRevenus as $item)
                        <div class="group transition-all duration-200">
                            <div class="flex items-center justify-between gap-3 text-sm">
                                <a href="{{ route('artisan.finances.show', $item['chantier']) }}"
                                   class="font-medium text-foreground hover:text-primary hover:underline decoration-primary underline-offset-2 transition-all truncate">
                                    {{ $item['nom'] }}
                                </a>
                                <span class="font-semibold text-foreground whitespace-nowrap">
                                    {{ number_format($item['montant'], 0, ',', ' ') }} FCFA
                                </span>
                            </div>
                            <x-blade-components::progress-bar
                                :progress="$maxRevenu > 0 ? (int) round(($item['montant'] / $maxRevenu) * 100) : 0"
                                class="mt-2"
                            />
                        </div>
                    @empty
                        <p class="text-sm text-muted-foreground">Aucun revenu pour le moment.</p>
                    @endforelse
                </div>
            </x-card>

            <!-- Dépenses par type -->
            <x-card class="rounded-2xl shadow-sm p-6 transition-all duration-300">
                <h3 class="text-sm font-semibold uppercase tracking-wider text-muted-foreground mb-5">
                    Dépenses par type
                </h3>
                <div class="space-y-4">
                    @forelse ($depensesParType as $categorie => $montant)
                        <div class="flex items-center justify-between gap-3 text-sm group">
                            <span class="font-medium text-foreground">
                                {{ $categorieLabels[$categorie] ?? ucfirst((string) $categorie) }}
                            </span>
                            <span class="font-semibold text-red-500 whitespace-nowrap transition-colors group-hover:text-red-400">
                                {{ number_format($montant, 0, ',', ' ') }} FCFA
                            </span>
                        </div>
                    @empty
                        <p class="text-sm text-muted-foreground">Aucune dépense pour le moment.</p>
                    @endforelse
                </div>
            </x-card>

            <!-- Dernières dépenses -->
            <x-card class="mt-6 rounded-2xl shadow-sm overflow-hidden transition-all duration-300">
                <div class="px-6 py-4 border-b border-border">
                    <h3 class="text-lg font-semibold text-foreground">Dernières dépenses</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-800">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Chantier</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Type</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Date</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Montant</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @forelse ($dernieresDepenses as $depense)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                    <td class="px-6 py-3 text-sm text-gray-900 dark:text-gray-100">
                                        <a href="{{ route('artisan.finances.show', $depense->chantier) }}" class="hover:text-primary transition-colors">
                                            {{ $depense->chantier->nom ?? '�?"' }}
                                        </a>
                                    </td>
                                    <td class="px-6 py-3 text-sm text-gray-900 dark:text-gray-100">
                                        <x-blade-components::badge style="primary">
                                            {{ $categorieLabels[$depense->categorie] ?? ucfirst($depense->categorie) }}
                                        </x-blade-components::badge>
                                    </td>
                                    <td class="px-6 py-3 text-sm text-gray-900 dark:text-gray-100">{{ $depense->date?->format('d/m/Y') }}</td>
                                    <td class="px-6 py-3 text-right text-sm font-medium text-red-600 dark:text-red-400">
                                        {{ number_format($depense->montant, 2, ',', ' ') }} FCFA
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-8 text-center text-sm text-gray-500 dark:text-gray-400">Aucune dépense enregistrée.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </x-card>
        </div>
    </div>
@endsection

