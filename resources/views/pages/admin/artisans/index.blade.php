@extends('layouts.dashboard')

@section('title', 'Gestion des artisans')

@section('content')
    @if (!$artisans->isEmpty())
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold text-gray-700">Gestion des artisans</h1>
                <p class="text-gray-500 text-xs mt-1">{{ $totalCount }} artisan(s) inscrit(s)</p>
            </div>
            <x-btn href="{{ route('admin.artisans.pending') }}">
                <x-slot:prefix>
                    <i data-lucide="clock"></i>
                </x-slot:prefix>
                En attente ({{ $pendingCount }})
            </x-btn>
        </div>

        <!-- Filtres -->
        <div class="bg-sidebar rounded-xl shadow-sm border border-gray-100 p-4 mb-6">
            <form action="{{ route('admin.artisans.index') }}" method="GET" class="flex gap-4">
                <div class="flex-1">
                    <div class="relative">
                        <i data-lucide="search" class="absolute left-3 top-1/2 transform -translate-y-1/2 w-4 h-4 text-gray-400"></i>
                        <input type="text" name="search" value="{{ request('search') }}"
                            placeholder="Rechercher un artisan..."
                            class="w-full pl-9 pr-4 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary focus:outline-hidden">
                    </div>
                </div>
                <x-btn type="submit">
                    <x-slot:prefix>
                        <i data-lucide="search"></i>
                    </x-slot:prefix>
                    Rechercher
                </x-btn>
            </form>
        </div>

        <x-container-dashed>
            <div x-data="artisanActions()" @keydown.escape="closeModal()">
                <div class="overflow-x-auto bg-sidebar rounded-lg shadow-sm">
                    <table class="w-full text-xs text-gray-600">
                        <thead class="border-b border-b-gray-100">
                            <tr>
                                <th class="px-4 py-3 text-left">ID</th>
                                <th class="px-4 py-3 text-left">Artisan</th>
                                <th class="px-4 py-3 text-left">Email</th>
                                <th class="px-4 py-3 text-left">Téléphone</th>
                                <th class="px-4 py-3 text-left">Statut</th>
                                <th class="px-4 py-3 text-center">Actions</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-gray-100">
                            @foreach ($artisans as $artisan)
                                <tr>
                                    <td class="px-4 py-3">#{{ $artisan->id }}</td>
                                    <td class="px-4 py-3">
                                        <div class="flex items-center gap-3">
                                            <div class="w-10 h-10 rounded-lg bg-gray-100 overflow-hidden flex-shrink-0">
                                                @if ($artisan->avatar)
                                                    <img src="{{ Storage::url($artisan->avatar) }}"
                                                        alt="{{ $artisan->business_name }}"
                                                        class="w-full h-full object-cover">
                                                @else
                                                    <div class="w-full h-full flex items-center justify-center text-gray-400 font-bold text-lg">
                                                        {{ substr($artisan->business_name, 0, 1) }}
                                                    </div>
                                                @endif
                                            </div>
                                            <div>
                                                <a href="{{ route('admin.artisans.show', $artisan) }}"
                                                    class="font-medium text-gray-900 hover:text-blue-600 transition">
                                                    {{ $artisan->business_name }}
                                                </a>
                                                <p class="text-xs text-gray-500">{{ $artisan->profession }} · {{ $artisan->city }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3">{{ $artisan->user->email }}</td>
                                    <td class="px-4 py-3">{{ $artisan->phone }}</td>
                                    <td class="px-4 py-3">
                                        <div class="flex flex-wrap gap-1">
                                            @if ($artisan->verified)
                                                <span class="px-2 py-1 text-xs font-medium text-emerald-600 bg-emerald-100 rounded-full">Vérifié</span>
                                            @else
                                                <span class="px-2 py-1 text-xs font-medium text-yellow-600 bg-yellow-100 rounded-full">En attente</span>
                                            @endif
                                            @if (!$artisan->is_active)
                                                <span class="px-2 py-1 text-xs font-medium text-red-600 bg-red-100 rounded-full">Suspendu</span>
                                            @endif
                                        </div>
                                    </td>

                                    <td class="px-4 py-3">
                                        <div class="flex items-center justify-center gap-2">
                                            @if (!$artisan->verified)
                                                <form action="{{ route('admin.artisans.verify', $artisan) }}" method="POST" class="inline">
                                                    @csrf
                                                    <button type="submit" class="block text-emerald-500 hover:text-emerald-700 transition" title="Valider">
                                                        <i data-lucide="check-circle" class="w-4 h-4"></i>
                                                    </button>
                                                </form>
                                            @endif

                                            <form action="{{ route('admin.artisans.suspend', $artisan) }}" method="POST" class="inline">
                                                @csrf
                                                <button type="submit" class="block text-yellow-500 hover:text-yellow-700 transition" title="{{ $artisan->is_active ? 'Suspendre' : 'Activer' }}">
                                                    <i data-lucide="{{ $artisan->is_active ? 'pause-circle' : 'play-circle' }}" class="w-4 h-4"></i>
                                                </button>
                                            </form>

                                            <a href="{{ route('admin.artisans.show', $artisan) }}" class="block text-blue-500 hover:text-blue-700 transition" title="Voir">
                                                <i data-lucide="eye" class="w-4 h-4"></i>
                                            </a>

                                            <button @click="openModal('{{ route('admin.artisans.destroy', $artisan) }}', '{{ $artisan->business_name }}')"
                                                    class="block text-destructive hover:text-red-700 transition" title="Supprimer">
                                                <i data-lucide="trash" class="w-4 h-4"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="mt-2 mb-2 text-xs">
                    {{ $artisans->links() }}
                </div>

                <!-- Modal de confirmation de suppression -->
                <div x-show="isOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50" @click.self="closeModal()">
                    <div class="relative w-full max-w-md rounded-lg bg-background p-6 shadow-lg" @click.stop>
                        <div class="flex items-start gap-4">
                            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-red-100">
                                <i data-lucide="alert-octagon" class="h-6 w-6 text-red-600"></i>
                            </div>
                            <div class="flex-1">
                                <h3 class="text-lg font-semibold text-gray-900">Supprimer l'artisan</h3>
                                <p class="mt-2 text-sm text-gray-600">
                                    Êtes-vous sûr de vouloir supprimer <strong x-text="itemTitle"></strong> ? Cette action est irréversible.
                                </p>
                                <p class="mt-2 text-xs text-red-600">
                                    Attention : Toutes les données liées à cet artisan seront également supprimées.
                                </p>
                            </div>
                        </div>

                        <div class="mt-6 flex items-center justify-end gap-3">
                            <x-btn @click="closeModal()" style="outline">
                                Annuler
                            </x-btn>
                            <form :action="deleteAction" method="POST" class="inline">
                                @csrf
                                @method('DELETE')
                                <x-btn type="submit" style="destructive">
                                    Supprimer
                                </x-btn>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </x-container-dashed>
    @else
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold text-gray-700">Gestion des artisans</h1>
                <p class="text-gray-500 text-xs mt-1">0 artisan(s) inscrit(s)</p>
            </div>
            <x-btn href="{{ route('admin.artisans.pending') }}">
                <x-slot:prefix>
                    <i data-lucide="clock"></i>
                </x-slot:prefix>
                En attente (0)
            </x-btn>
        </div>
        <x-empty title="Aucun artisan trouvé" description="Aucun artisan n'est actuellement inscrit sur la plateforme">
            <x-slot:icon>
                <i data-lucide="users"></i>
            </x-slot:icon>
        </x-empty>
    @endif

    <script>
        function artisanActions() {
            return {
                isOpen: false,
                deleteAction: '',
                itemTitle: '',
                openModal(action, title) {
                    this.deleteAction = action;
                    this.itemTitle = title;
                    this.isOpen = true;
                },
                closeModal() {
                    this.isOpen = false;
                    this.deleteAction = '';
                    this.itemTitle = '';
                }
            }
        }
    </script>
@endsection