@extends('layouts.base')

@section('title', 'Nouvelle conversation - Messagerie - Client')

@section('content')
    <x-ui.user-dashboard-nav />
    <x-blade-components::layout.container>
        <div class="container mx-auto px-4 py-8 max-w-2xl">
            <div class="mb-6">
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Nouveau message</h1>
                <p class="text-gray-500 dark:text-gray-400 mt-1 text-sm">Sélectionnez un artisan pour démarrer une conversation</p>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                <form method="POST" action="{{ route('client.messagerie.store') }}">
                    @csrf

                    <div class="mb-4">
                        <label for="artisan_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Artisan</label>
                        <select name="artisan_id" id="artisan_id" required class="w-full px-4 py-2.5 border border-gray-200 dark:border-gray-600 rounded-lg focus:border-orange-500 focus:ring-2 focus:ring-orange-100 dark:focus:ring-orange-900 outline-none transition bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                            <option value="">Sélectionner un artisan</option>
                            @foreach ($artisans as $artisan)
                                <option value="{{ $artisan->id }}">{{ $artisan->business_name }} - {{ $artisan->profession }}</option>
                            @endforeach
                        </select>
                        @error('artisan_id')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mb-6">
                        <label for="sujet" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Sujet (optionnel)</label>
                        <input type="text" name="sujet" id="sujet" maxlength="255" class="w-full px-4 py-2.5 border border-gray-200 dark:border-gray-600 rounded-lg focus:border-orange-500 focus:ring-2 focus:ring-orange-100 dark:focus:ring-orange-900 outline-none transition bg-white dark:bg-gray-700 text-gray-900 dark:text-white" placeholder="Ex: Demande de devis">
                        @error('sujet')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex gap-3">
                        <button type="submit" class="bg-orange-500 hover:bg-orange-600 text-white px-6 py-2.5 rounded-lg font-medium transition">
                            Créer la conversation
                        </button>
                        <a href="{{ route('client.messagerie.index') }}" class="bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 px-6 py-2.5 rounded-lg font-medium transition">
                            Annuler
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </x-blade-components::layout.container>
@endsection