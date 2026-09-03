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
                <a href="{{ route('notifications.api') }}"
                   class="relative w-10 h-10 rounded-full bg-card border border-border flex items-center justify-center text-muted-foreground hover:text-foreground hover:border-primary transition-all duration-200 hover:rotate-[-5deg]"
                   aria-label="Notifications">
                    <i data-lucide="bell" class="w-5 h-5"></i>
                </a>
                <!-- Nouveau projet -->
                <a href="{{ route('artisan.chantiers.create') }}"
                   class="inline-flex items-center gap-2 px-5 py-2.5 bg-primary hover:bg-primary/90 text-white font-medium rounded-full transition-all duration-200 hover:scale-105 hover:shadow-lg hover:shadow-primary/30 active:scale-95">
                    <i data-lucide="plus" class="w-4 h-4"></i>
                    Nouveau projet
                </a>
            </div>
        </div>

        <!-- ===== SOUS-HEADER ===== -->
        <div class="flex flex-wrap items-center justify-between gap-4 bg-card border border-border rounded-2xl shadow-sm px-5 py-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-primary/10 dark:bg-primary/20 flex items-center justify-center transition-all duration-300 hover:scale-110">
                    <i data-lucide="pie-chart" class="w-5 h-5 text-primary"></i>
                </div>
                <div>
                    <p class="font-semibold text-foreground">Centre financier</p>
                    <p class="text-xs text-muted-foreground">Vue d'ensemble de vos finances</p>
                </div>
            </div>
            <button type="button"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-zinc-800 hover:bg-zinc-700 text-white text-sm font-medium rounded-lg transition-all duration-200 active:scale-95 hover:shadow-lg">
                <i data-lucide="download" class="w-4 h-4"></i>
                Exporter le bilan
            </button>
        </div>

        <!-- ===== CARTES KPI ===== -->
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4">
            @foreach ($kpis as $kpi)
                <div class="bg-card border border-border border-l-4 {{ $kpi['border'] }} rounded-2xl shadow-sm p-5 transition-all duration-300 ease-in-out hover:scale-[1.02] hover:shadow-2xl">
                    <p class="text-[11px] font-semibold uppercase tracking-wider text-muted-foreground">
                        {{ $kpi['label'] }}
                    </p>
                    <p class="mt-3 text-xl sm:text-2xl font-bold text-foreground truncate">
                        {{ number_format($kpi['value'], 0, ',', ' ') }}
                        <span class="text-xs sm:text-sm font-medium {{ $kpi['text'] }}">
                            {{ $kpi['suffix'] }}
                        </span>
                    </p>
                </div>
            @endforeach
        </div>
        <!-- Barre de recherche -->
        <div class="mb-4">
            @include('components.artisan.search-bar', ['placeholder' => 'Rechercher une transaction�?�'])
        </div>

        <!-- ===== Enregistrement rapide d'une dépense ===== -->
        <div class="bg-card border border-border rounded-2xl shadow-sm p-6 transition-all duration-300 hover:shadow-lg">
            <div class="flex items-center justify-between gap-4 mb-4">
                <div>
                    <h3 class="text-lg font-semibold text-foreground">Nouvelle dépense</h3>
                    <p class="text-sm text-muted-foreground">Enregistrez rapidement une sortie d'argent.</p>
                </div>
            </div>

            <form id="depense-quick-form" method="POST" action="{{ route('artisan.finances.store-depense', $chantiersList->first()?->id ?? 0) }}" enctype="multipart/form-data" class="grid grid-cols-1 md:grid-cols-5 gap-4 items-end">
                @csrf
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-foreground mb-2">Chantier *</label>
                    <select name="chantier_id" id="depense-chantier" required class="w-full rounded-lg border-border focus:border-primary focus:ring-primary bg-background">
                        <option value="">Sélectionner un chantier</option>
                        @foreach($chantiersList as $chantier)
                            <option value="{{ $chantier->id }}">{{ $chantier->nom }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-foreground mb-2">Type *</label>
                    <select name="categorie" required class="w-full rounded-lg border-border focus:border-primary focus:ring-primary bg-background">
                        <option value="materiaux">Matériaux</option>
                        <option value="main_oeuvre">Main d'oeuvre</option>
                        <option value="transport">Transport</option>
                        <option value="autre">Autre</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-foreground mb-2">Montant *</label>
                    <input type="number" step="0.01" name="montant" required class="w-full rounded-lg border-border focus:border-primary focus:ring-primary bg-background" placeholder="Ex: 15000">
                </div>
                <div>
                    <label class="block text-sm font-medium text-foreground mb-2">Date</label>
                    <input type="date" name="date" value="{{ now()->format('Y-m-d') }}" class="w-full rounded-lg border-border focus:border-primary focus:ring-primary bg-background">
                </div>
                <div class="md:col-span-5">
                    <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 bg-primary hover:bg-primary/90 text-white rounded-lg transition-all duration-200 hover:scale-105 active:scale-95">
                        <i data-lucide="save" class="w-4 h-4"></i>
                        Enregistrer la dépense
                    </button>
                </div>
            </form>
        </div>

        <script>
            (function () {
                const form = document.getElementById('depense-quick-form');
                const select = document.getElementById('depense-chantier');
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
            <div class="bg-card border border-border rounded-2xl shadow-sm p-6 transition-all duration-300 hover:scale-[1.01] hover:shadow-2xl">
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
                            <div class="mt-2 h-2 w-full bg-muted rounded-full overflow-hidden">
                                <div class="h-full bg-primary rounded-full transition-all group-hover:brightness-110"
                                     style="width: {{ $maxRevenu > 0 ? (int) round(($item['montant'] / $maxRevenu) * 100) : 0 }}%"></div>
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-muted-foreground">Aucun revenu pour le moment.</p>
                    @endforelse
                </div>
            </div>

            <!-- Dépenses par type -->
            <div class="bg-card border border-border rounded-2xl shadow-sm p-6 transition-all duration-300 hover:scale-[1.01] hover:shadow-2xl">
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
            </div>

            <!-- Dernières dépenses -->
            <div class="mt-6 bg-card border border-border rounded-2xl shadow-sm overflow-hidden transition-all duration-300 hover:shadow-lg">
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
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-primary/10 dark:bg-primary/20 text-primary dark:text-white/80">
                                            {{ $categorieLabels[$depense->categorie] ?? ucfirst($depense->categorie) }}
                                        </span>
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
            </div>
        </div>
    </div>
@endsection

