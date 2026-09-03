@extends('layouts.artisan')

@section('title', 'Mon équipe - Artisan')

@section('breadcrumbs')
    <nav class="flex items-center gap-2 text-sm text-muted-foreground">
        <i data-lucide="user-check" class="w-4 h-4"></i>
        <span>Mon équipe</span>
    </nav>
@endsection

@section('content')
<div class="container mx-auto px-4 py-8" x-data="equipeApp()">
    <!-- En-tête -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-8">
        <div>
            <h1 class="text-xl md:text-2xl font-bold text-gray-900 dark:text-white">Mon <span class="text-primary">équipe</span></h1>
            <p class="text-gray-500 dark:text-gray-400 mt-1 text-sm">Gérez vos collaborateurs</p>
        </div>
        <x-btn href="{{ route('artisan.equipe.create') }}" size="lg">
            <x-slot:prefix><i data-lucide="plus" class="w-4 h-4"></i></x-slot:prefix>
            + Nouveau membre
        </x-btn>
    </div>

    <!-- Statistiques -->
    <div class="grid grid-cols-2 md:grid-cols-3 gap-3 mb-6">
        <div class="bg-white dark:bg-gray-800 rounded-xl p-3 border border-gray-100 dark:border-gray-700 shadow-sm flex items-center gap-3">
            <div class="w-11 h-11 bg-primary/10 dark:bg-primary/20 rounded-full flex items-center justify-center text-xl"><i data-lucide="wallet" class="w-4 h-4 inline-block align-middle mr-1"></i></div>
            <div>
                <div class="text-xl font-bold text-gray-900 dark:text-white">{{ $stats['total'] }}</div>
                <div class="text-xs text-gray-500 dark:text-gray-400">Total</div>
            </div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl p-3 border border-gray-100 dark:border-gray-700 shadow-sm flex items-center gap-3">
            <div class="w-11 h-11 bg-emerald-50 dark:bg-emerald-900/30 rounded-full flex items-center justify-center text-xl"><i data-lucide="circle-check" class="w-4 h-4 inline-block align-middle mr-1"></i></div>
            <div>
                <div class="text-xl font-bold text-emerald-600 dark:text-emerald-400">{{ $stats['actif'] }}</div>
                <div class="text-xs text-gray-500 dark:text-gray-400">Actifs</div>
            </div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl p-3 border border-gray-100 dark:border-gray-700 shadow-sm flex items-center gap-3">
            <div class="w-11 h-11 bg-red-50 dark:bg-red-900/30 rounded-full flex items-center justify-center text-red-600 dark:text-red-300"><i data-lucide="pause" class="w-5 h-5"></i></div>
            <div>
                <div class="text-xl font-bold text-red-600 dark:text-red-400">{{ $stats['inactif'] }}</div>
                <div class="text-xs text-gray-500 dark:text-gray-400">Inactifs</div>
            </div>
        </div>
    </div>

        <!-- Barre de recherche -->
    <div class="mb-4">
        @include('components.artisan.search-bar', ['placeholder' => 'Rechercher un membre'])
    </div>

    <!-- Filtres -->
    <div class="flex flex-wrap gap-2 items-center mb-6">
        <a href="{{ route('artisan.equipe.index') }}"
            class="px-4 py-1.5 rounded-full text-sm font-medium border transition
                {{ !request('statut') && !request('search') ? 'bg-gray-900 text-white border-gray-900 dark:bg-white dark:text-gray-900' : 'bg-white dark:bg-gray-800 text-gray-500 dark:text-gray-400 border-gray-200 dark:border-gray-700 hover:border-primary' }}">
            <i data-lucide="chart-no-axes-column" class="w-4 h-4 inline-block align-middle mr-1"></i> Tous
        </a>
        @foreach ($statuts as $statut)
            <a href="{{ route('artisan.equipe.index', ['statut' => $statut->value]) }}"
                class="px-4 py-1.5 rounded-full text-sm font-medium border transition
                    {{ request('statut') === $statut->value ? 'bg-gray-900 text-white border-gray-900 dark:bg-white dark:text-gray-900' : 'bg-white dark:bg-gray-800 text-gray-500 dark:text-gray-400 border-gray-200 dark:border-gray-700 hover:border-primary' }}">
                {{ $statut->label() }}
            </a>
        @endforeach
        <span class="text-xs text-gray-400 dark:text-gray-500 ml-2">{{ $membres->total() }} membre(s)</span>
    </div>

    <!-- Grille des membres -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
        @forelse ($membres as $membre)
            <div class="bg-white dark:bg-gray-800 rounded-xl p-5 shadow-sm border border-gray-100 dark:border-gray-700 transition hover:-translate-y-1 hover:shadow-md hover:border-primary/40 dark:hover:border-primary">
                <div class="flex justify-between items-start mb-3">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 rounded-full bg-primary/10 dark:bg-primary/20 flex items-center justify-center text-lg font-bold text-primary dark:text-primary">
                            {{ $membre->initial }}
                        </div>
                        <div>
                            <div class="font-semibold text-gray-900 dark:text-white">{{ $membre->nom }}</div>
                            <div class="text-xs text-gray-500 dark:text-gray-400">{{ $membre->role }}</div>
                        </div>
                    </div>
                    <span class="text-xs font-semibold px-2 py-0.5 rounded-full {{ $membre->statut->colorClass() }}">
                        {{ $membre->statut->label() }}
                    </span>
                </div>

                <div class="space-y-2 text-sm text-gray-600 dark:text-gray-400">
                    @if ($membre->telephone)
                        <div class="flex items-center gap-2">
                            <i data-lucide="phone" class="w-4 h-4"></i>
                            <a href="tel:{{ $membre->telephone }}" class="hover:text-primary transition-colors">{{ $membre->telephone }}</a>
                        </div>
                    @endif
                    @if ($membre->email)
                        <div class="flex items-center gap-2">
                            <i data-lucide="mail" class="w-4 h-4"></i>
                            <a href="mailto:{{ $membre->email }}" class="hover:text-primary transition-colors truncate">{{ $membre->email }}</a>
                        </div>
                    @endif
                </div>

                <div class="flex gap-2 mt-4 pt-3 border-t border-gray-100 dark:border-gray-700">
                    <x-btn href="{{ route('artisan.equipe.show', $membre) }}" class="flex-1" size="sm">
                        Voir détails
                    </x-btn>
                    <x-btn href="{{ route('artisan.equipe.edit', $membre) }}" style="info" size="icon" title="Modifier">
                        <i data-lucide="pencil"></i>
                    </x-btn>
                    <form method="POST" action="{{ route('artisan.equipe.destroy', $membre) }}" class="inline" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce membre ?');">
                        @csrf
                        @method('DELETE')
                        <x-btn type="submit" style="destructive" size="icon" title="Supprimer">
                            <i data-lucide="trash-2"></i>
                        </x-btn>
                    </form>
                </div>
            </div>
        @empty
            <div class="col-span-full text-center py-16 text-gray-500 dark:text-gray-400">
                <div class="text-5xl mb-2"><i data-lucide="wallet" class="w-4 h-4 inline-block align-middle mr-1"></i></div>
                <p>Aucun membre pour le moment.</p>
                <p class="text-sm mt-1">Ajoutez votre premier membre avec le bouton ci-dessus.</p>
            </div>
        @endforelse
    </div>

    <!-- Pagination -->
    <div class="mt-6">
        {{ $membres->links() }}
    </div>

    <!-- Modal Nouveau Membre -->
    <div x-show="modalOpen" x-cloak class="fixed inset-0 bg-black/50 backdrop-blur-sm z-[1000] flex items-center justify-center p-4">
        <div class="bg-white dark:bg-gray-800 rounded-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto p-6 shadow-2xl">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-xl font-bold text-gray-900 dark:text-white"><i data-lucide="wallet" class="w-4 h-4 inline-block align-middle mr-1"></i> Nouveau membre</h2>
                <button @click="modalOpen = false" class="text-2xl text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">&times;</button>
            </div>

            <form method="POST" action="{{ route('artisan.equipe.store') }}">
                @csrf
                <div class="space-y-4">
                    <div>
                        <label class="block font-semibold text-sm mb-1 text-gray-900 dark:text-white">Nom complet *</label>
                        <input type="text" name="nom" value="{{ old('nom') }}" required
                            class="w-full px-4 py-2.5 border border-gray-200 dark:border-gray-600 rounded-lg focus:border-primary focus:ring-2 focus:ring-primary/10 dark:focus:ring-primary/20 outline-none transition bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                        @error('nom') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block font-semibold text-sm mb-1 text-gray-900 dark:text-white">Rôle *</label>
                        <input type="text" name="role" value="{{ old('role') }}" required placeholder="Ex: Chef de chantier, Apprenti..."
                            class="w-full px-4 py-2.5 border border-gray-200 dark:border-gray-600 rounded-lg focus:border-primary focus:ring-2 focus:ring-primary/10 dark:focus:ring-primary/20 outline-none transition bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                        @error('role') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block font-semibold text-sm mb-1 text-gray-900 dark:text-white">Téléphone *</label>
                            <input type="text" name="telephone" value="{{ old('telephone') }}" required
                                class="w-full px-4 py-2.5 border border-gray-200 dark:border-gray-600 rounded-lg focus:border-primary focus:ring-2 focus:ring-primary/10 dark:focus:ring-primary/20 outline-none transition bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                            @error('telephone') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block font-semibold text-sm mb-1 text-gray-900 dark:text-white">Email</label>
                            <input type="email" name="email" value="{{ old('email') }}"
                                class="w-full px-4 py-2.5 border border-gray-200 dark:border-gray-600 rounded-lg focus:border-primary focus:ring-2 focus:ring-primary/10 dark:focus:ring-primary/20 outline-none transition bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                            @error('email') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div>
                        <label class="block font-semibold text-sm mb-1 text-gray-900 dark:text-white">Statut *</label>
                        <x-form.select name="statut" label="Statut *" :options="$statuts->mapWithKeys(fn ($statut) => [$statut->value => $statut->label()])" required />
                    </div>
                </div>

                <div class="mt-6">
                    <button type="submit" class="w-full bg-primary hover:bg-primary/90 text-white py-3 rounded-xl font-semibold transition">Ajouter le membre</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('equipeApp', () => ({
            modalOpen: false,

            openModal() {
                this.modalOpen = true;
            }
        }));
    });
</script>
@endpush
@endsection