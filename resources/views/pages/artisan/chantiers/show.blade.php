@extends('layouts.artisan')

@section('title', $chantier->nom.' - Chantier - Artisan')

@section('breadcrumbs')
    <nav class="flex items-center gap-2 text-sm text-muted-foreground">
        <a href="{{ route('artisan.chantiers.index') }}" class="hover:text-foreground transition-colors flex items-center gap-1">
            <i data-lucide="hard-hat" class="w-4 h-4"></i>
            <span>Mes chantiers</span>
        </a>
        <span class="text-muted-foreground">/</span>
        <span class="text-foreground font-medium truncate max-w-48">{{ $chantier->nom }}</span>
    </nav>
@endsection

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- En-tête -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-8">
        <div>
            <h1 class="text-xl md:text-2xl font-bold text-gray-900 dark:text-white">{{ $chantier->nom }}</h1>
            <p class="text-gray-500 dark:text-gray-400 mt-1 text-sm">
                Créé le {{ $chantier->created_at->format('d/m/Y') }}
                · {{ $chantier->client?->name ?? 'Client non assigné' }}
            </p>
        </div>
        <div class="flex gap-2">
            <form method="POST" action="{{ route('artisan.chantiers.destroy', $chantier) }}" onsubmit="return confirm('Supprimer ce chantier ?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="px-4 py-2 rounded-full text-sm font-medium border border-red-300 text-red-600 hover:bg-red-50 dark:border-red-800 dark:text-red-400 dark:hover:bg-red-900/20 transition">
                    Supprimer
                </button>
            </form>
            <a href="{{ route('artisan.chantiers.index') }}" class="px-4 py-2 rounded-full text-sm font-medium border border-gray-300 text-gray-600 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-400 dark:hover:bg-gray-800 transition">
                ← Retour
            </a>
        </div>
    </div>

    <!-- Grille d'information -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Informations générales -->
        <div class="bg-sidebar dark:bg-gray-800 rounded-xl p-5 border border-accent dark:border-gray-700">
            <h3 class="text-xs uppercase font-semibold text-gray-400 dark:text-gray-500 mb-3 border-b border-gray-100 dark:border-gray-700 pb-2">Informations</h3>
            <div class="space-y-2 text-sm">
                <div class="flex justify-between">
                    <span class="text-gray-500 dark:text-gray-400">Client</span>
                    <span class="font-medium text-gray-900 dark:text-white">{{ $chantier->client?->name ?? 'Non assigné' }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500 dark:text-gray-400">Email</span>
                    <span class="font-medium text-gray-900 dark:text-white">{{ $chantier->client?->email ?? '—' }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500 dark:text-gray-400">Type</span>
                    <span class="font-medium text-gray-900 dark:text-white">{{ $types[$chantier->type] ?? $chantier->type }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500 dark:text-gray-400">Budget</span>
                    <span class="font-bold text-gray-900 dark:text-white">{{ number_format($chantier->budget ?? 0, 0, ',', ' ') }} FCFA HT</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500 dark:text-gray-400">Statut</span>
                    <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full text-xs font-semibold {{ $chantier->statut->colorClass() }}">
                        <span class="w-1.5 h-1.5 rounded-full {{ $chantier->statut->dotColorClass() }}"></span>
                        {{ $chantier->statut->label() }}
                    </span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500 dark:text-gray-400">Priorité</span>
                    <span class="font-medium text-gray-900 dark:text-white">
                        @switch($chantier->priorite)
                            @case('haute') 🔴 Haute @break
                            @case('moyenne') 🟡 Moyenne @break
                            @case('basse') 🟢 Basse @break
                            @default —
                        @endswitch
                    </span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500 dark:text-gray-400">Date début</span>
                    <span class="font-medium text-gray-900 dark:text-white">{{ $chantier->date_debut?->format('d/m/Y') ?? '—' }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500 dark:text-gray-400">Date fin</span>
                    <span class="font-medium text-gray-900 dark:text-white">{{ $chantier->date_fin?->format('d/m/Y') ?? '—' }}</span>
                </div>
            </div>
        </div>

        <!-- Paiements -->
        <div class="bg-sidebar dark:bg-gray-800 rounded-xl p-5 border border-accent dark:border-gray-700">
            <h3 class="text-xs uppercase font-semibold text-gray-400 dark:text-gray-500 mb-3 border-b border-gray-100 dark:border-gray-700 pb-2">💰 Paiements</h3>
            <div class="space-y-3">
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-600 dark:text-gray-400">Acompte (30%)</span>
                    <span class="font-bold text-gray-900 dark:text-white">{{ number_format(($chantier->budget ?? 0) * 0.3, 0, ',', ' ') }} FCFA</span>
                    <span class="text-xs font-semibold px-2 py-0.5 rounded-full {{ $chantier->acompte_paye ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300' : 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300' }}">
                        {{ $chantier->acompte_paye ? '✅ Payé' : '⏳ En attente' }}
                    </span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-600 dark:text-gray-400">Solde (70%)</span>
                    <span class="font-bold text-gray-900 dark:text-white">{{ number_format(($chantier->budget ?? 0) * 0.7, 0, ',', ' ') }} FCFA</span>
                    <span class="text-xs font-semibold px-2 py-0.5 rounded-full {{ $chantier->solde_paye ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300' : 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300' }}">
                        {{ $chantier->solde_paye ? '✅ Payé' : '⏳ En attente' }}
                    </span>
                </div>
                <div class="flex justify-between items-center pt-2 border-t border-gray-100 dark:border-gray-700">
                    <span class="text-sm font-semibold text-gray-700 dark:text-gray-300">Réception</span>
                    <span class="text-xs font-semibold px-2 py-0.5 rounded-full {{ $chantier->reception_validee ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300' : 'bg-gray-100 text-gray-500 dark:bg-gray-700 dark:text-gray-400' }}">
                        {{ $chantier->reception_validee ? '✅ Validée' : '⏳ En attente' }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Matériel -->
        @if ($chantier->materiel)
        <div class="bg-sidebar dark:bg-gray-800 rounded-xl p-5 border border-accent dark:border-gray-700">
            <h3 class="text-xs uppercase font-semibold text-gray-400 dark:text-gray-500 mb-3 border-b border-gray-100 dark:border-gray-700 pb-2">🔧 Matériel nécessaire</h3>
            <p class="text-sm text-gray-700 dark:text-gray-300 whitespace-pre-wrap">{{ $chantier->materiel }}</p>
        </div>
        @endif

        <!-- Note client -->
        @if ($chantier->note_client)
        <div class="bg-sidebar dark:bg-gray-800 rounded-xl p-5 border border-accent dark:border-gray-700">
            <h3 class="text-xs uppercase font-semibold text-gray-400 dark:text-gray-500 mb-3 border-b border-gray-100 dark:border-gray-700 pb-2">📝 Note client</h3>
            <p class="text-sm text-gray-700 dark:text-gray-300 whitespace-pre-wrap">{{ $chantier->note_client }}</p>
        </div>
        @endif

        <!-- Avancement -->
        <div class="bg-sidebar dark:bg-gray-800 rounded-xl p-5 border border-accent dark:border-gray-700">
            <h3 class="text-xs uppercase font-semibold text-gray-400 dark:text-gray-500 mb-3 border-b border-gray-100 dark:border-gray-700 pb-2">📋 Avancement</h3>
            <div class="relative w-full h-2 bg-gray-200 dark:bg-gray-700 rounded-full overflow-hidden mb-2">
                <div class="h-full bg-orange-500 rounded-full transition-all" style="width: {{ $chantier->progress }}%"></div>
            </div>
            <span class="text-xs text-gray-500 dark:text-gray-400">{{ $chantier->progress }}%</span>

            <div class="flex gap-2 mt-3 flex-wrap">
                <span class="px-2 py-0.5 rounded-full text-xs font-semibold {{ in_array($chantier->statut->value, ['devis','en_cours','attente','arret','termine']) ? 'bg-orange-500 text-white' : 'bg-gray-200 text-gray-400' }}">Devis</span>
                <span class="px-2 py-0.5 rounded-full text-xs font-semibold {{ in_array($chantier->statut->value, ['en_cours','attente','arret','termine']) ? 'bg-orange-500 text-white' : 'bg-gray-200 text-gray-400' }}">Chantier</span>
                <span class="px-2 py-0.5 rounded-full text-xs font-semibold {{ in_array($chantier->statut->value, ['attente','termine']) ? 'bg-orange-500 text-white' : ($chantier->statut->value === 'en_cours' ? 'bg-gray-900 text-white' : 'bg-gray-200 text-gray-400') }}">Validation</span>
                <span class="px-2 py-0.5 rounded-full text-xs font-semibold {{ $chantier->statut->value === 'termine' ? 'bg-orange-500 text-white' : 'bg-gray-200 text-gray-400' }}">Livré</span>
            </div>
        </div>

        <!-- Checklist -->
        <div class="bg-sidebar dark:bg-gray-800 rounded-xl p-5 border border-accent dark:border-gray-700">
            <h3 class="text-xs uppercase font-semibold text-gray-400 dark:text-gray-500 mb-3 border-b border-gray-100 dark:border-gray-700 pb-2">✅ Points de contrôle</h3>
            @php $checklist = $chantier->checklist ?? []; @endphp
            @if (!empty($checklist))
                <div class="space-y-2">
                    @foreach ($checklist as $item)
                        @php $label = is_array($item) ? ($item['label'] ?? '') : $item; $done = is_array($item) && ($item['done'] ?? false); @endphp
                        <div class="flex items-center gap-2 py-1 border-b border-gray-50 dark:border-gray-700/50 {{ $done ? 'line-through text-gray-400 dark:text-gray-500' : 'text-gray-700 dark:text-gray-300' }}">
                            <input type="checkbox" {{ $done ? 'checked' : '' }} disabled class="w-4 h-4 accent-orange-500">
                            <label class="text-sm">{{ $label }}</label>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-sm text-gray-500 dark:text-gray-400">Aucun point de contrôle défini.</p>
            @endif
        </div>
    </div>

    <!-- Actions de statut -->
    <div class="mt-8 bg-sidebar dark:bg-gray-800 rounded-xl p-5 border border-accent dark:border-gray-700">
        <h3 class="text-xs uppercase font-semibold text-gray-400 dark:text-gray-500 mb-3 border-b border-gray-100 dark:border-gray-700 pb-2">Actions</h3>
        <div class="flex flex-wrap gap-2">
            @if ($chantier->statut->value !== 'termine')
                @if ($chantier->statut->value !== 'arret')
                    <form method="POST" action="{{ route('artisan.chantiers.update', $chantier) }}" class="inline">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="statut" value="en_cours">
                        <button type="submit" class="bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 px-4 py-2 rounded-full text-sm font-medium transition text-gray-800 dark:text-gray-200">▶️ Démarrer</button>
                    </form>
                    <form method="POST" action="{{ route('artisan.chantiers.update', $chantier) }}" class="inline">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="statut" value="attente">
                        <button type="submit" class="bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 px-4 py-2 rounded-full text-sm font-medium transition text-gray-800 dark:text-gray-200">⏳ En attente</button>
                    </form>
                @endif
                <form method="POST" action="{{ route('artisan.chantiers.update', $chantier) }}" class="inline">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="statut" value="arret">
                    <button type="submit" class="bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 px-4 py-2 rounded-full text-sm font-medium transition text-gray-800 dark:text-gray-200">🛑 En arrêt</button>
                </form>
                <form method="POST" action="{{ route('artisan.chantiers.update', $chantier) }}" class="inline">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="statut" value="termine">
                    <button type="submit" class="bg-emerald-500 hover:bg-emerald-600 text-white px-4 py-2 rounded-full text-sm font-medium transition">✅ Terminer</button>
                </form>
            @endif
        </div>
    </div>
</div>
@endsection