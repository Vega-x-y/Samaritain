@extends('layouts.dashboard')

@section('title', 'Villes')

@section('content')
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-gray-800 dark:text-white">Villes</h1>
        <x-btn href="{{ route('admin.configuration.city.create') }}">
            <x-slot:prefix><i data-lucide="plus"></i></x-slot:prefix>
            Ajouter une ville
        </x-btn>
    </div>

    <form method="GET" action="{{ route('admin.configuration.city.index') }}" class="flex items-center gap-2 mb-4">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Rechercher une ville..." class="w-full max-w-sm h-9 rounded-lg text-sm border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 text-gray-800 dark:text-white">
        <x-btn type="submit" style="outline">Rechercher</x-btn>
    </form>

    <x-container-dashed>
        <div class="overflow-x-auto bg-sidebar dark:bg-gray-800 rounded-lg shadow-sm">
            <table class="w-full text-xs text-gray-600 dark:text-gray-300">
                <thead class="border-b border-gray-100 dark:border-gray-700"><tr><th class="px-4 py-3 text-left">Nom</th><th class="px-4 py-3 text-center">Arrondissements</th><th class="px-4 py-3 text-center">Actions</th></tr></thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    @forelse ($cities as $city)
                        <tr><td class="px-4 py-3 font-medium text-gray-800 dark:text-white">{{ $city->name }}</td><td class="px-4 py-3 text-center">{{ $city->arrondissements_count }}</td><td class="px-4 py-3 text-center"><a href="{{ route('admin.configuration.city.edit', $city) }}" title="Modifier"><i data-lucide="edit" class="inline w-4 h-4 text-blue-500"></i></a><form action="{{ route('admin.configuration.city.destroy', $city) }}" method="POST" class="inline ml-3" onsubmit="return confirm('Supprimer cette ville ?')">@csrf @method('DELETE')<button type="submit" title="Supprimer"><i data-lucide="trash" class="inline w-4 h-4 text-red-500"></i></button></form></td></tr>
                    @empty
                        <tr><td colspan="3" class="px-4 py-8 text-center">Aucune ville trouvée.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-2">{{ $cities->links() }}</div>
    </x-container-dashed>
@endsection
