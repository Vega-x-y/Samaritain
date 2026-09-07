@extends('layouts.dashboard')

@section('title', 'Les bureaux')

@section('content')
    @php
        $propertyType = 'bureau';
        $adminRoutePrefix = 'admin.bureau';
        $propertyLabel = 'bureau';
    @endphp
    @if (!$bureaus->isEmpty())
        <div class="flex justify-between">
            <h1 class="text-gray-800 dark:text-white">Liste des bureaux</h1>
            <x-btn href="{{ route($adminRoutePrefix . '.create') }}" class="dark:bg-primary-600 dark:text-white dark:hover:bg-primary-700">
                <x-slot:prefix>
                    <i data-lucide="plus"></i>
                </x-slot:prefix>
                Créer un bureau
            </x-btn>
        </div>
        <x-container-dashed>
            <div x-data="deleteModal()" @keydown.escape="closeModal()">
                <div class="overflow-x-auto bg-sidebar dark:bg-gray-800 rounded-lg shadow-sm">
                    <table class="w-full text-xs text-gray-600 dark:text-gray-300">
                        <thead class="border-b border-b-gray-100 dark:border-b-gray-700">
                            <tr>
                                <th class="px-4 py-3 text-left">ID</th>
                                <th class="px-4 py-3 text-left">Nom</th>
                                <th class="px-4 py-3 text-left">Localisation</th>
                                <th class="px-4 py-3 text-left">Téléphone</th>
                                <th class="px-4 py-3 text-left">Email</th>
                                <th class="px-4 py-3 text-left">Statut</th>
                                <th class="px-4 py-3 text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                            @foreach ($bureaus as $bureau)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition">
                                    <td class="px-4 py-3">#{{ $loop->iteration }}</td>
                                    <td class="px-4 py-3 font-medium hover:text-blue-600 dark:hover:text-blue-400">
                                        <a href="{{ route($adminRoutePrefix . '.show', $bureau) }}" class="text-gray-800 dark:text-white">{{ $bureau->name }}</a>
                                    </td>
                                    <td class="px-4 py-3 text-gray-800 dark:text-white">{{ $bureau->location ?? '-' }}</td>
                                    <td class="px-4 py-3">{{ $bureau->phone ?? '-' }}</td>
                                    <td class="px-4 py-3">{{ $bureau->email ?? '-' }}</td>
                                    <td class="px-4 py-3">
                                        @if ($bureau->active)
                                            <span class="px-3 py-1 text-xs font-semibold text-green-600 dark:text-green-400 bg-green-100 dark:bg-green-900/30 rounded-full">
                                                Actif
                                            </span>
                                        @else
                                            <span class="px-3 py-1 text-xs font-semibold text-red-600 dark:text-red-400 bg-red-100 dark:bg-red-900/30 rounded-full">
                                                Inactif
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <div class="flex justify-center gap-2">
                                            <a href="{{ route($adminRoutePrefix . '.show', $bureau) }}" class="text-blue-600 dark:text-blue-400 hover:underline">
                                                <i data-lucide="eye" class="w-4 h-4"></i>
                                            </a>
                                            <a href="{{ route($adminRoutePrefix . '.edit', $bureau) }}" class="text-amber-600 dark:text-amber-400 hover:underline">
                                                <i data-lucide="edit" class="w-4 h-4"></i>
                                            </a>
                                            <button type="button" @click="openModal('{{ route($adminRoutePrefix . '.destroy', $bureau) }}')" class="text-red-600 dark:text-red-400 hover:underline">
                                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <x-delete-modal :route="$adminRoutePrefix" />
            </div>
        </x-container-dashed>
    @else
        <div class="text-center py-12">
            <i data-lucide="inbox" class="w-12 h-12 text-gray-400 dark:text-gray-600 mx-auto mb-4"></i>
            <h3 class="text-lg font-medium text-gray-800 dark:text-white mb-2">Aucun bureau trouvé</h3>
            <p class="text-gray-600 dark:text-gray-400 mb-4">Commencez en ajoutant votre premier bureau</p>
            <x-btn href="{{ route($adminRoutePrefix . '.create') }}" class="dark:bg-primary-600 dark:text-white dark:hover:bg-primary-700">
                <x-slot:prefix>
                    <i data-lucide="plus"></i>
                </x-slot:prefix>
                Créer un bureau
            </x-btn>
        </div>
    @endif
@endsection
