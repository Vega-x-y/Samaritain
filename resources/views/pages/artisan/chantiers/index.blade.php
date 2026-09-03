@extends('layouts.artisan')

@section('title', 'Mes chantiers - Artisan')

@section('breadcrumbs')
    <nav class="flex items-center gap-2 text-sm text-muted-foreground">
        <i data-lucide="hard-hat" class="w-4 h-4"></i>
        <span>Mes chantiers</span>
    </nav>
@endsection

@section('content')
<div class="container mx-auto px-4 py-8" x-data="chantierApp()">
    <!-- En-tête -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-8">
        <div>
            <h1 class="text-xl md:text-2xl font-bold text-gray-900 dark:text-white">Mes <span class="text-primary">chantiers</span></h1>
            <p class="text-gray-500 dark:text-gray-400 mt-1 text-sm">Gérez vos projets et suivez leur avancement</p>
        </div>
        <x-btn href="{{ route('artisan.chantiers.create') }}" size="lg">
            <x-slot:prefix><i data-lucide="plus" class="w-4 h-4"></i></x-slot:prefix>
            Nouveau chantier
        </x-btn>
    </div>

    <!-- Statistiques -->
    <div class="grid grid-cols-2 md:grid-cols-5 gap-3 mb-6">
        <div class="bg-white dark:bg-gray-800 rounded-xl p-3 border border-gray-100 dark:border-gray-700 shadow-sm flex items-center gap-3">
            <div class="w-11 h-11 bg-primary/10 dark:bg-primary/20 rounded-full flex items-center justify-center text-xl"><i data-lucide="clipboard-list" class="w-4 h-4 inline-block align-middle mr-1"></i></div>
            <div>
                <div class="text-xl font-bold text-gray-900 dark:text-white">{{ $stats['total'] }}</div>
                <div class="text-xs text-gray-500 dark:text-gray-400">Total</div>
            </div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl p-3 border border-gray-100 dark:border-gray-700 shadow-sm flex items-center gap-3">
            <div class="w-11 h-11 bg-blue-50 dark:bg-blue-900/30 rounded-full flex items-center justify-center text-xl"><i data-lucide="zap" class="w-4 h-4 inline-block align-middle mr-1"></i></div>
            <div>
                <div class="text-xl font-bold text-blue-600 dark:text-blue-400">{{ $stats['en_cours'] }}</div>
                <div class="text-xs text-gray-500 dark:text-gray-400">En cours</div>
            </div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl p-3 border border-gray-100 dark:border-gray-700 shadow-sm flex items-center gap-3">
            <div class="w-11 h-11 bg-amber-50 dark:bg-amber-900/30 rounded-full flex items-center justify-center text-xl"><i data-lucide="clock-3" class="w-4 h-4 inline-block align-middle mr-1"></i></div>
            <div>
                <div class="text-xl font-bold text-amber-600 dark:text-amber-400">{{ $stats['attente'] }}</div>
                <div class="text-xs text-gray-500 dark:text-gray-400">En attente</div>
            </div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl p-3 border border-gray-100 dark:border-gray-700 shadow-sm flex items-center gap-3">
            <div class="w-11 h-11 bg-emerald-50 dark:bg-emerald-900/30 rounded-full flex items-center justify-center text-xl"><i data-lucide="circle-check" class="w-4 h-4 inline-block align-middle mr-1"></i></div>
            <div>
                <div class="text-xl font-bold text-emerald-600 dark:text-emerald-400">{{ $stats['termine'] }}</div>
                <div class="text-xs text-gray-500 dark:text-gray-400">Terminés</div>
            </div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl p-3 border border-gray-100 dark:border-gray-700 shadow-sm flex items-center gap-3">
            <div class="w-11 h-11 bg-red-50 dark:bg-red-900/30 rounded-full flex items-center justify-center text-xl"><i data-lucide="octagon-stop" class="w-4 h-4 inline-block align-middle mr-1"></i>'</div>
            <div>
                <div class="text-xl font-bold text-red-600 dark:text-red-400">{{ $stats['arret'] }}</div>
                <div class="text-xs text-gray-500 dark:text-gray-400">En arrêt</div>
            </div>
        </div>
    </div>

        <!-- Barre de recherche -->
    <div class="mb-4">
        @include('components.artisan.search-bar', ['placeholder' => 'Rechercher un chantier'])
    </div>

    <!-- Filtres -->
    <div class="flex flex-wrap gap-2 items-center mb-6">
        <a href="{{ route('artisan.chantiers.index') }}"
            class="px-4 py-1.5 rounded-full text-sm font-medium border transition
                {{ !request('statut') && !request('type') ? 'bg-gray-900 text-white border-gray-900 dark:bg-white dark:text-gray-900' : 'bg-white dark:bg-gray-800 text-gray-500 dark:text-gray-400 border-gray-200 dark:border-gray-700 hover:border-primary' }}">
            <i data-lucide="chart-no-axes-column" class="w-4 h-4 inline-block align-middle mr-1"></i> Tous
        </a>
        @foreach ($types as $value => $label)
            <a href="{{ route('artisan.chantiers.index', ['type' => $value]) }}"
                class="px-4 py-1.5 rounded-full text-sm font-medium border transition
                    {{ request('type') === $value ? 'bg-gray-900 text-white border-gray-900 dark:bg-white dark:text-gray-900' : 'bg-white dark:bg-gray-800 text-gray-500 dark:text-gray-400 border-gray-200 dark:border-gray-700 hover:border-primary' }}">
                {{ $label }}
            </a>
        @endforeach
        <span class="text-xs text-gray-400 dark:text-gray-500 ml-2">{{ $chantiers->total() }} chantier(s)</span>
    </div>

    <!-- Grille des chantiers -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
        @forelse ($chantiers as $chantier)
            <div class="bg-white dark:bg-gray-800 rounded-xl p-5 shadow-sm border border-gray-100 dark:border-gray-700 transition hover:border-primary/40 dark:hover:border-primary">
                <div class="flex justify-between items-start">
                    <div>
                        <div class="font-semibold text-lg text-gray-900 dark:text-white">{{ $chantier->nom }}</div>
                        <div class="text-sm text-gray-500 dark:text-gray-400">
                            {{ $chantier->client?->nom ?? 'Client non assigné' }}
                        </div>
                    </div>
                    <span class="text-xs font-semibold uppercase px-2 py-0.5 rounded-full"
                        @class([
                            'bg-blue-50 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300' => $chantier->type === 'plomberie',
                            'bg-primary/10 text-primary dark:bg-primary/20 dark:text-white/80' => $chantier->type === 'electricite',
                            'bg-pink-50 text-pink-700 dark:bg-pink-900/30 dark:text-pink-300' => $chantier->type === 'peinture',
                            'bg-emerald-50 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300' => $chantier->type === 'maconnerie',
                            'bg-lime-50 text-lime-700 dark:bg-lime-900/30 dark:text-lime-300' => $chantier->type === 'menuiserie',
                        ])>
                        {{ $types[$chantier->type] ?? $chantier->type }}
                    </span>
                </div>

                <div class="flex flex-wrap gap-4 text-sm text-gray-500 dark:text-gray-400 mt-3">
                    <span><i data-lucide="calendar-days" class="w-4 h-4 inline-block align-middle mr-1"></i> {{ $chantier->created_at->format('d/m/Y') }}</span>
                    <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full text-xs font-semibold {{ $chantier->statut->colorClass() }}">
                        <span class="w-1.5 h-1.5 rounded-full {{ $chantier->statut->dotColorClass() }}"></span>
                        {{ $chantier->statut->label() }}
                    </span>
                </div>

                <div class="flex justify-between items-center mt-4 pt-3 border-t border-gray-100 dark:border-gray-700">
                    <span class="font-bold text-lg text-gray-900 dark:text-white">{{ number_format($chantier->budget ?? 0, 0, ',', ' ') }} FCFA</span>
                    <span class="text-xs text-gray-500 dark:text-gray-400 bg-gray-50 dark:bg-gray-700 px-3 py-0.5 rounded-full">{{ $chantier->progress }}%</span>
                </div>
                <!-- Boutons de changement de statut -->
                <div class="flex flex-wrap gap-1.5 mt-3">
                    @php
                        $statutsButtons = [
                            'termine' => ['label' => 'Terminé', 'cls' => 'bg-emerald-500 hover:bg-emerald-600'],
                            'en_cours' => ['label' => 'En cours', 'cls' => 'bg-primary hover:bg-primary/90'],
                            'arret' => ['label' => 'En arrêt', 'cls' => 'bg-red-500 hover:bg-red-600'],
                            'attente' => ['label' => 'En attente', 'cls' => 'bg-amber-500 hover:bg-amber-600'],
                        ];
                    @endphp
                    @foreach ($statutsButtons as $statutValue => $btn)
                        @if ($chantier->statut->value !== $statutValue)
                            <form method="POST" action="{{ route('artisan.chantiers.statut', $chantier) }}" class="inline">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="statut" value="{{ $statutValue }}">
                                <button type="submit" class="text-white text-xs px-2.5 py-1.5 rounded-full font-medium transition {{ $btn['cls'] }}">
                                    {{ $btn['label'] }}
                                </button>
                            </form>
                        @endif
                    @endforeach
                </div>
                <div class="flex gap-2 mt-3">
                    <a href="{{ route('artisan.chantiers.edit', $chantier) }}" class="flex-1 bg-blue-500 hover:bg-blue-600 text-white text-center py-2 rounded-full text-sm font-medium transition">
                        <i data-lucide="pencil" class="w-4 h-4 inline-block align-middle mr-1"></i> Modifier
                    </a>
                    <form method="POST" action="{{ route('artisan.chantiers.destroy', $chantier) }}" class="inline" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce chantier ?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="px-3 py-2 bg-red-500 hover:bg-red-600 text-white rounded-full text-sm font-medium transition">
                            <i data-lucide="trash-2" class="w-4 h-4 inline-block align-middle mr-1"></i>
                        </button>
                    </form>
                </div>
            </div>
        @empty
            <div class="col-span-full text-center py-16 text-gray-500 dark:text-gray-400">
                <div class="text-5xl mb-2"><i data-lucide="clipboard-list" class="w-4 h-4 inline-block align-middle mr-1"></i></div>
                <p>Aucun chantier pour le moment.</p>
                <p class="text-sm mt-1">Créez votre premier chantier avec le bouton ci-dessus.</p>
            </div>
        @endforelse
    </div>

    <!-- Pagination -->
    <div class="mt-6">
        {{ $chantiers->links() }}
    </div>

    <!-- Modal Nouveau Chantier -->
    <div x-show="modalOpen" x-cloak class="fixed inset-0 bg-black/50 backdrop-blur-sm z-[1000] flex items-center justify-center p-4">
        <div class="bg-white dark:bg-gray-800 rounded-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto p-6 shadow-2xl">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-xl font-bold text-gray-900 dark:text-white"><i data-lucide="clipboard-list" class="w-4 h-4 inline-block align-middle mr-1"></i> Nouveau chantier</h2>
                <button @click="modalOpen = false" class="text-2xl text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">&times;</button>
            </div>

            <form method="POST" action="{{ route('artisan.chantiers.store') }}">
                @csrf
                <div class="space-y-4">
                    <div>
                        <label class="block font-semibold text-sm mb-1 text-gray-900 dark:text-white">Nom du projet *</label>
                        <input type="text" name="nom" value="{{ old('nom') }}" required
                            class="w-full px-4 py-2.5 border border-gray-200 dark:border-gray-600 rounded-lg focus:border-primary focus:ring-2 focus:ring-primary/10 dark:focus:ring-primary/20 outline-none transition bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                        @error('nom') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block font-semibold text-sm mb-1 text-gray-900 dark:text-white">Client</label>
                        <select name="client_id"
                            class="w-full px-4 py-2.5 border border-gray-200 dark:border-gray-600 rounded-lg focus:border-primary focus:ring-2 focus:ring-primary/10 dark:focus:ring-primary/20 outline-none transition bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                            <option value="">-- Sélectionner un client --</option>
                            @foreach ($clients as $client)
                                <option value="{{ $client->id }}" {{ old('client_id') == $client->id ? 'selected' : '' }}>{{ $client->nom }}</option>
                            @endforeach
                        </select>
                        @error('client_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block font-semibold text-sm mb-1 text-gray-900 dark:text-white">Type *</label>
                        <select name="type" required
                            class="w-full px-4 py-2.5 border border-gray-200 dark:border-gray-600 rounded-lg focus:border-primary focus:ring-2 focus:ring-primary/10 dark:focus:ring-primary/20 outline-none transition bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                            @foreach ($types as $value => $label)
                                <option value="{{ $value }}" {{ old('type') === $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('type') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block font-semibold text-sm mb-1 text-gray-900 dark:text-white">Budget (FCFA HT)</label>
                            <input type="number" name="budget" value="{{ old('budget') }}"
                                class="w-full px-4 py-2.5 border border-gray-200 dark:border-gray-600 rounded-lg focus:border-primary focus:ring-2 focus:ring-primary/10 dark:focus:ring-primary/20 outline-none transition bg-white dark:bg-gray-700 text-gray-900 dark:text-white" min="0" step="100">
                            @error('budget') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block font-semibold text-sm mb-1 text-gray-900 dark:text-white">Priorité</label>
                            <select name="priorite"
                                class="w-full px-4 py-2.5 border border-gray-200 dark:border-gray-600 rounded-lg focus:border-primary focus:ring-2 focus:ring-primary/10 dark:focus:ring-primary/20 outline-none transition bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                                <option value="">-- Priorité --</option>
                                <option value="haute" {{ old('priorite') === 'haute' ? 'selected' : '' }}><i data-lucide="package" class="w-4 h-4 inline-block align-middle mr-1"></i> Haute</option>
                                <option value="moyenne" {{ old('priorite') === 'moyenne' ? 'selected' : '' }}>Moyenne</option>
                                <option value="basse" {{ old('priorite') === 'basse' ? 'selected' : '' }}>Basse</option>
                            </select>
                            @error('priorite') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block font-semibold text-sm mb-1 text-gray-900 dark:text-white">Date de début</label>
                            <input type="date" name="date_debut" value="{{ old('date_debut') }}"
                                class="w-full px-4 py-2.5 border border-gray-200 dark:border-gray-600 rounded-lg focus:border-primary focus:ring-2 focus:ring-primary/10 dark:focus:ring-primary/20 outline-none transition bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                            @error('date_debut') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block font-semibold text-sm mb-1 text-gray-900 dark:text-white">Date de fin</label>
                            <input type="date" name="date_fin" value="{{ old('date_fin') }}"
                                class="w-full px-4 py-2.5 border border-gray-200 dark:border-gray-600 rounded-lg focus:border-primary focus:ring-2 focus:ring-primary/10 dark:focus:ring-primary/20 outline-none transition bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                            @error('date_fin') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div>
                        <label class="block font-semibold text-sm mb-1 text-gray-900 dark:text-white">Matériel nécessaire</label>
                        <textarea name="materiel" rows="2"
                            class="w-full px-4 py-2.5 border border-gray-200 dark:border-gray-600 rounded-lg focus:border-primary focus:ring-2 focus:ring-primary/10 dark:focus:ring-primary/20 outline-none transition bg-white dark:bg-gray-700 text-gray-900 dark:text-white">{{ old('materiel') }}</textarea>
                        @error('materiel') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block font-semibold text-sm mb-1 text-gray-900 dark:text-white">Note client</label>
                        <textarea name="note_client" rows="2"
                            class="w-full px-4 py-2.5 border border-gray-200 dark:border-gray-600 rounded-lg focus:border-primary focus:ring-2 focus:ring-primary/10 dark:focus:ring-primary/20 outline-none transition bg-white dark:bg-gray-700 text-gray-900 dark:text-white">{{ old('note_client') }}</textarea>
                        @error('note_client') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block font-semibold text-sm mb-1 text-gray-900 dark:text-white">Points de contrôle</label>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mb-2">Ajoutez les étapes du chantier</p>
                        <div class="space-y-2">
                            <template x-for="(item, index) in checklist" :key="index">
                                <div class="flex items-center gap-2">
                                    <input type="text" x-model="checklist[index]" :name="'checklist['+index+']'"
                                        class="flex-1 px-3 py-1.5 border border-gray-200 dark:border-gray-600 rounded-lg text-sm focus:border-primary focus:ring-2 focus:ring-primary/10 outline-none transition bg-white dark:bg-gray-700 text-gray-900 dark:text-white" placeholder="Nom de l'étape">
                                    <button type="button" @click="checklist.splice(index, 1)" class="text-red-500 hover:text-red-700 text-sm font-bold">&times;</button>
                                </div>
                            </template>
                        </div>
                        <button type="button" @click="checklist.push('')" class="mt-2 text-sm text-primary hover:text-primary font-medium">+ Ajouter une étape</button>
                    </div>
                </div>

                <div class="mt-6">
                    <button type="submit" class="w-full bg-primary hover:bg-primary/90 text-white py-3 rounded-xl font-semibold transition">Créer le chantier</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('chantierApp', () => ({
            modalOpen: false,
            checklist: ['Démolition', 'Installation', 'Finition', 'Réception client'],

            openModal() {
                this.modalOpen = true;
                this.checklist = ['Démolition', 'Installation', 'Finition', 'Réception client'];
            }
        }));
    });
</script>
@endpush
@endsection