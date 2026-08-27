@extends('layouts.dashboard')

@section('title', 'Arrondissements')

@section('content')
    @if (!$arrondissements->isEmpty())
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-gray-800 dark:text-white">Arrondissements</h1>
            <x-btn href="{{ route('admin.configuration.arrondissement.create') }}">
                <x-slot:prefix><i data-lucide="plus"></i></x-slot:prefix>
                Ajouter un arrondissement
            </x-btn>
        </div>

        <form method="GET" action="{{ route('admin.configuration.arrondissement.index') }}" class="flex items-center gap-2 mb-4">
            <div class="relative flex-1 max-w-sm">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <i data-lucide="search" class="w-4 h-4 text-gray-400 dark:text-gray-500"></i>
                </div>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Rechercher un arrondissement..."
                    class="w-full h-9 rounded-lg text-sm border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 pl-10 pr-4 py-2 text-gray-800 dark:text-white focus:outline-hidden focus:ring-2 focus:border-primary dark:focus:border-primary focus:ring-primary/10 dark:focus:ring-primary/20">
            </div>
            <x-btn type="submit" style="outline">Rechercher</x-btn>
            @if (request('search'))
                <a href="{{ route('admin.configuration.arrondissement.index') }}" class="text-xs text-gray-500 dark:text-gray-400 hover:text-primary">
                    Réinitialiser
                </a>
            @endif
        </form>

        <x-container-dashed>
            <div class="overflow-x-auto bg-sidebar dark:bg-gray-800 rounded-lg shadow-sm">
                <table class="w-full text-xs text-gray-600 dark:text-gray-300">
                    <thead class="border-b border-gray-100 dark:border-gray-700">
                        <tr>
                            <th class="px-4 py-3 text-left">Arrondissement</th>
                            <th class="px-4 py-3 text-left">Ville</th>
                            <th class="px-4 py-3 text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                        @foreach ($arrondissements as $arrondissement)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition">
                                <td class="px-4 py-3 font-medium text-gray-800 dark:text-white">{{ $arrondissement->name }}</td>
                                <td class="px-4 py-3">{{ $arrondissement->city->name }}</td>
                                <td class="px-4 py-3 text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        <a href="{{ route('admin.configuration.arrondissement.edit', $arrondissement) }}" title="Modifier" class="text-blue-500 dark:text-blue-400 hover:text-blue-600 dark:hover:text-blue-300">
                                            <i data-lucide="edit" class="w-4 h-4"></i>
                                        </a>
                                        <form action="{{ route('admin.configuration.arrondissement.destroy', $arrondissement) }}" method="POST" class="inline" onsubmit="return confirm('Supprimer cet arrondissement ?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" title="Supprimer" class="text-red-500 dark:text-red-400 hover:text-red-600 dark:hover:text-red-300">
                                                <i data-lucide="trash" class="w-4 h-4"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="mt-2 text-xs text-gray-600 dark:text-gray-400">
                {{ $arrondissements->links() }}
            </div>
        </x-container-dashed>
    @else
        <div class="flex justify-between items-center mb-6">
            <div></div>
            <x-btn href="{{ route('admin.configuration.arrondissement.create') }}">
                <x-slot:prefix><i data-lucide="plus"></i></x-slot:prefix>
                Ajouter le premier arrondissement
            </x-btn>
        </div>

        <x-empty title="Aucun arrondissement trouvé" description="Créez un premier arrondissement pour commencer" class="dark:text-gray-400">
            <x-slot:icon>
                <i data-lucide="map-pin" class="text-gray-400 dark:text-gray-500"></i>
            </x-slot:icon>
        </x-empty>
    @endif
@endsection
