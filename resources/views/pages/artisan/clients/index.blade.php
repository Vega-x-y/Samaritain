@extends('layouts.artisan')

@section('title', 'Mes clients - Artisan')

@section('breadcrumbs')
    <nav class="flex items-center gap-2 text-sm text-muted-foreground">
        <i data-lucide="users" class="w-4 h-4"></i>
        <span>Mes clients</span>
    </nav>
@endsection

@section('content')
<div class="container mx-auto px-4 py-8" x-data="clientApp()">
    <!-- En-tête -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-8">
        <div>
            <h1 class="text-xl md:text-2xl font-bold text-gray-900 dark:text-white">Mes <span class="text-orange-500">clients</span></h1>
            <p class="text-gray-500 dark:text-gray-400 mt-1 text-sm">Gérez votre carnet d'adresses</p>
        </div>
        <a href="{{ route('artisan.clients.create') }}" class="bg-orange-500 hover:bg-orange-600 text-white px-5 py-2.5 rounded-full font-semibold text-sm transition shadow-md hover:shadow-lg">
            + Nouveau client
        </a>
    </div>

    <!-- Statistiques -->
    <div class="grid grid-cols-2 md:grid-cols-3 gap-3 mb-6">
        <div class="bg-sidebar dark:bg-gray-800 rounded-xl p-3 border border-accent dark:border-gray-700 shadow-sm flex items-center gap-3">
            <div class="w-11 h-11 bg-orange-50 dark:bg-orange-900/30 rounded-full flex items-center justify-center text-xl">👥</div>
            <div>
                <div class="text-xl font-bold text-gray-900 dark:text-white">{{ $stats['total'] }}</div>
                <div class="text-xs text-gray-500 dark:text-gray-400">Total</div>
            </div>
        </div>
        <div class="bg-sidebar dark:bg-gray-800 rounded-xl p-3 border border-accent dark:border-gray-700 shadow-sm flex items-center gap-3">
            <div class="w-11 h-11 bg-blue-50 dark:bg-blue-900/30 rounded-full flex items-center justify-center text-xl">👤</div>
            <div>
                <div class="text-xl font-bold text-blue-600 dark:text-blue-400">{{ $stats['particulier'] }}</div>
                <div class="text-xs text-gray-500 dark:text-gray-400">Particuliers</div>
            </div>
        </div>
        <div class="bg-sidebar dark:bg-gray-800 rounded-xl p-3 border border-accent dark:border-gray-700 shadow-sm flex items-center gap-3">
            <div class="w-11 h-11 bg-purple-50 dark:bg-purple-900/30 rounded-full flex items-center justify-center text-xl">🏢</div>
            <div>
                <div class="text-xl font-bold text-purple-600 dark:text-purple-400">{{ $stats['entreprise'] }}</div>
                <div class="text-xs text-gray-500 dark:text-gray-400">Entreprises</div>
            </div>
        </div>
    </div>

        <!-- Barre de recherche -->
    <div class="mb-4">
        @include('components.artisan.search-bar', ['placeholder' => 'Rechercher un client…'])
    </div>

    <!-- Filtres -->
    <div class="flex flex-wrap gap-2 items-center mb-6">
        <a href="{{ route('artisan.clients.index') }}"
            class="px-4 py-1.5 rounded-full text-sm font-medium border transition
                {{ !request('type') && !request('search') ? 'bg-gray-900 text-white border-gray-900 dark:bg-white dark:text-gray-900' : 'bg-white dark:bg-gray-800 text-gray-500 dark:text-gray-400 border-gray-200 dark:border-gray-700 hover:border-orange-500' }}">
            📊 Tous
        </a>
        @foreach ($types as $type)
            <a href="{{ route('artisan.clients.index', ['type' => $type->value]) }}"
                class="px-4 py-1.5 rounded-full text-sm font-medium border transition
                    {{ request('type') === $type->value ? 'bg-gray-900 text-white border-gray-900 dark:bg-white dark:text-gray-900' : 'bg-white dark:bg-gray-800 text-gray-500 dark:text-gray-400 border-gray-200 dark:border-gray-700 hover:border-orange-500' }}">
                {{ $type->icon() }} {{ $type->label() }}
            </a>
        @endforeach
        <span class="text-xs text-gray-400 dark:text-gray-500 ml-2">{{ $clients->total() }} client(s)</span>
    </div>

    <!-- Grille des clients -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
        @forelse ($clients as $client)
            <div class="bg-sidebar dark:bg-gray-800 rounded-xl p-5 shadow-sm border border-accent dark:border-gray-700 transition hover:-translate-y-1 hover:shadow-md hover:border-orange-300 dark:hover:border-orange-700">
                <div class="flex justify-between items-start mb-3">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 rounded-full bg-orange-100 dark:bg-orange-900/30 flex items-center justify-center text-lg font-bold text-orange-600 dark:text-orange-400">
                            {{ $client->initial }}
                        </div>
                        <div>
                            <div class="font-semibold text-gray-900 dark:text-white">{{ $client->nom }}</div>
                            <span class="text-xs px-2 py-0.5 rounded-full {{ $client->type->value === 'particulier' ? 'bg-blue-50 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300' : 'bg-purple-50 text-purple-700 dark:bg-purple-900/30 dark:text-purple-300' }}">
                                {{ $client->type->icon() }} {{ $client->type->label() }}
                            </span>
                        </div>
                    </div>
                </div>

                <div class="space-y-2 text-sm text-gray-600 dark:text-gray-400">
                    @if ($client->telephone)
                        <div class="flex items-center gap-2">
                            <i data-lucide="phone" class="w-4 h-4"></i>
                            <a href="tel:{{ $client->telephone }}" class="hover:text-orange-500 transition-colors">{{ $client->telephone }}</a>
                        </div>
                    @endif
                    @if ($client->email)
                        <div class="flex items-center gap-2">
                            <i data-lucide="mail" class="w-4 h-4"></i>
                            <a href="mailto:{{ $client->email }}" class="hover:text-orange-500 transition-colors truncate">{{ $client->email }}</a>
                        </div>
                    @endif
                    @if ($client->adresse)
                        <div class="flex items-start gap-2">
                            <i data-lucide="map-pin" class="w-4 h-4 mt-0.5"></i>
                            <span class="line-clamp-2">{{ $client->adresse }}</span>
                        </div>
                    @endif
                </div>

                <div class="flex gap-2 mt-4 pt-3 border-t border-gray-100 dark:border-gray-700">
                    <a href="{{ route('artisan.clients.show', $client) }}" class="flex-1 bg-orange-500 hover:bg-orange-600 text-white text-center py-2 rounded-full text-sm font-medium transition">
                        Voir détails
                    </a>
                    <a href="{{ route('artisan.clients.edit', $client) }}" class="px-3 py-2 bg-blue-500 hover:bg-blue-600 text-white rounded-full text-sm font-medium transition" title="Modifier">
                        ✏️
                    </a>
                    <form method="POST" action="{{ route('artisan.clients.destroy', $client) }}" class="inline" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce client ?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="px-3 py-2 bg-red-500 hover:bg-red-600 text-white rounded-full text-sm font-medium transition" title="Supprimer">
                            🗑️
                        </button>
                    </form>
                </div>
            </div>
        @empty
            <div class="col-span-full text-center py-16 text-gray-500 dark:text-gray-400">
                <div class="text-5xl mb-2">👥</div>
                <p>Aucun client pour le moment.</p>
                <p class="text-sm mt-1">Créez votre premier client avec le bouton ci-dessus.</p>
            </div>
        @endforelse
    </div>

    <!-- Pagination -->
    <div class="mt-6">
        {{ $clients->links() }}
    </div>

    <!-- Modal Nouveau Client -->
    <div x-show="modalOpen" x-cloak class="fixed inset-0 bg-black/50 backdrop-blur-sm z-[1000] flex items-center justify-center p-4">
        <div class="bg-white dark:bg-gray-800 rounded-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto p-6 shadow-2xl">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-xl font-bold text-gray-900 dark:text-white">👤 Nouveau client</h2>
                <button @click="modalOpen = false" class="text-2xl text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">&times;</button>
            </div>

            <form method="POST" action="{{ route('artisan.clients.store') }}">
                @csrf
                <div class="space-y-4">
                    <div>
                        <label class="block font-semibold text-sm mb-1 text-gray-900 dark:text-white">Nom complet *</label>
                        <input type="text" name="nom" value="{{ old('nom') }}" required
                            class="w-full px-4 py-2.5 border border-gray-200 dark:border-gray-600 rounded-lg focus:border-orange-500 focus:ring-2 focus:ring-orange-100 dark:focus:ring-orange-900 outline-none transition bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                        @error('nom') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block font-semibold text-sm mb-1 text-gray-900 dark:text-white">Téléphone *</label>
                            <input type="text" name="telephone" value="{{ old('telephone') }}" required
                                class="w-full px-4 py-2.5 border border-gray-200 dark:border-gray-600 rounded-lg focus:border-orange-500 focus:ring-2 focus:ring-orange-100 dark:focus:ring-orange-900 outline-none transition bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                            @error('telephone') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block font-semibold text-sm mb-1 text-gray-900 dark:text-white">Email</label>
                            <input type="email" name="email" value="{{ old('email') }}"
                                class="w-full px-4 py-2.5 border border-gray-200 dark:border-gray-600 rounded-lg focus:border-orange-500 focus:ring-2 focus:ring-orange-100 dark:focus:ring-orange-900 outline-none transition bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                            @error('email') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div>
                        <label class="block font-semibold text-sm mb-1 text-gray-900 dark:text-white">Adresse</label>
                        <input type="text" name="adresse" value="{{ old('adresse') }}"
                            class="w-full px-4 py-2.5 border border-gray-200 dark:border-gray-600 rounded-lg focus:border-orange-500 focus:ring-2 focus:ring-orange-100 dark:focus:ring-orange-900 outline-none transition bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                        @error('adresse') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block font-semibold text-sm mb-1 text-gray-900 dark:text-white">Type *</label>
                            <select name="type" required
                                class="w-full px-4 py-2.5 border border-gray-200 dark:border-gray-600 rounded-lg focus:border-orange-500 focus:ring-2 focus:ring-orange-100 dark:focus:ring-orange-900 outline-none transition bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                                @foreach ($types as $type)
                                    <option value="{{ $type->value }}" {{ old('type') === $type->value ? 'selected' : '' }}>
                                        {{ $type->icon() }} {{ $type->label() }}
                                    </option>
                                @endforeach
                            </select>
                            @error('type') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div>
                        <label class="block font-semibold text-sm mb-1 text-gray-900 dark:text-white">Notes</label>
                        <textarea name="notes" rows="3"
                            class="w-full px-4 py-2.5 border border-gray-200 dark:border-gray-600 rounded-lg focus:border-orange-500 focus:ring-2 focus:ring-orange-100 dark:focus:ring-orange-900 outline-none transition bg-white dark:bg-gray-700 text-gray-900 dark:text-white">{{ old('notes') }}</textarea>
                        @error('notes') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="mt-6">
                    <button type="submit" class="w-full bg-orange-500 hover:bg-orange-600 text-white py-3 rounded-xl font-semibold transition">Créer le client</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('clientApp', () => ({
            modalOpen: false,

            openModal() {
                this.modalOpen = true;
            }
        }));
    });
</script>
@endpush
@endsection