@extends('layouts.base')

@section('title', 'Tableau de bord - Bureaux')

@section('content')
    <div class="container mx-auto px-4 py-8">
        <div class="flex justify-between items-center mb-8">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Mes bureaux</h1>
                <p class="text-gray-600 dark:text-gray-400 mt-1">Gérez vos bureaux</p>
            </div>
            <a href="{{ route('bureau.create') }}" class="inline-flex items-center gap-2 px-6 py-3 bg-primary text-white rounded-lg hover:bg-primary-600 dark:hover:bg-primary-700 font-medium transition">
                <i data-lucide="plus" class="w-5 h-5"></i>
                Nouveau bureau
            </a>
        </div>

        @if(!$bureaus->isEmpty())
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach ($bureaus as $bureau)
                    <div class="bg-card dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden hover:shadow-lg transition">
                        <div class="p-6">
                            <div class="flex items-start justify-between mb-4">
                                <div>
                                    <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-1">{{ $bureau->name }}</h3>
                                    <div class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400">
                                        <i data-lucide="map-pin" class="w-4 h-4"></i>
                                        <span>{{ $bureau->location ?? 'Localisation non spécifiée' }}</span>
                                    </div>
                                </div>
                                @if($bureau->active)
                                    <span class="px-2 py-1 text-xs font-medium text-green-600 dark:text-green-400 bg-green-100 dark:bg-green-900/30 rounded-full">
                                        Actif
                                    </span>
                                @else
                                    <span class="px-2 py-1 text-xs font-medium text-red-600 dark:text-red-400 bg-red-100 dark:bg-red-900/30 rounded-full">
                                        Inactif
                                    </span>
                                @endif
                            </div>

                            @if($bureau->description)
                                <p class="text-sm text-gray-600 dark:text-gray-400 mb-4 line-clamp-2">{{ $bureau->description }}</p>
                            @endif

                            <div class="space-y-2 mb-4 text-sm text-gray-600 dark:text-gray-400">
                                @if($bureau->phone)
                                    <div class="flex items-center gap-2">
                                        <i data-lucide="phone" class="w-4 h-4"></i>
                                        <span>{{ $bureau->phone }}</span>
                                    </div>
                                @endif
                                @if($bureau->email)
                                    <div class="flex items-center gap-2">
                                        <i data-lucide="mail" class="w-4 h-4"></i>
                                        <span>{{ $bureau->email }}</span>
                                    </div>
                                @endif
                            </div>

                            <div class="flex gap-2 pt-4 border-t border-gray-200 dark:border-gray-700">
                                <a href="{{ route('bureau.show', $bureau) }}" class="flex-1 inline-flex items-center justify-center gap-2 px-3 py-2 bg-gray-100 dark:bg-gray-700 text-gray-900 dark:text-white rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition text-sm font-medium">
                                    <i data-lucide="eye" class="w-4 h-4"></i>
                                    Voir
                                </a>
                                <a href="{{ route('bureau.edit', $bureau) }}" class="flex-1 inline-flex items-center justify-center gap-2 px-3 py-2 bg-primary text-white rounded-lg hover:bg-primary-600 dark:hover:bg-primary-700 transition text-sm font-medium">
                                    <i data-lucide="edit" class="w-4 h-4"></i>
                                    Modifier
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Pagination -->
            <div class="mt-12">
                {{ $bureaus->links() }}
            </div>
        @else
            <div class="text-center py-12 bg-card dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700">
                <i data-lucide="inbox" class="w-12 h-12 text-gray-400 dark:text-gray-600 mx-auto mb-4"></i>
                <h3 class="text-lg font-medium text-gray-800 dark:text-white mb-2">Aucun bureau créé</h3>
                <p class="text-gray-600 dark:text-gray-400 mb-4">Commencez en créant votre premier bureau</p>
                <a href="{{ route('bureau.create') }}" class="inline-flex items-center gap-2 px-6 py-2 bg-primary text-white rounded-lg hover:bg-primary-600 dark:hover:bg-primary-700 font-medium transition">
                    <i data-lucide="plus" class="w-4 h-4"></i>
                    Créer un bureau
                </a>
            </div>
        @endif
    </div>
@endsection
