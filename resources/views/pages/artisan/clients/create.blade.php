@extends('layouts.artisan')

@section('title', 'Nouveau client - Artisan')

@section('breadcrumbs')
    <nav class="flex items-center gap-2 text-sm text-muted-foreground">
        <a href="{{ route('artisan.clients.index') }}" class="hover:text-orange-500 transition-colors">
            <i data-lucide="users" class="w-4 h-4"></i>
            <span>Mes clients</span>
        </a>
        <i data-lucide="chevron-right" class="w-4 h-4"></i>
        <span>Nouveau client</span>
    </nav>
@endsection

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- En-tête -->
    <div class="mb-8">
        <h1 class="text-xl md:text-2xl font-bold text-gray-900 dark:text-white">👤 Nouveau <span class="text-orange-500">client</span></h1>
        <p class="text-gray-500 dark:text-gray-400 mt-1 text-sm">Ajoutez un nouveau contact à votre carnet d'adresses</p>
    </div>

    <div class="max-w-3xl">
        <form method="POST" action="{{ route('artisan.clients.store') }}" class="space-y-6">
            @csrf

            <div class="bg-sidebar dark:bg-gray-800 rounded-xl p-6 shadow-sm border border-accent dark:border-gray-700">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Informations du client</h2>

                <div class="space-y-4">
                    <div>
                        <label class="block font-semibold text-sm mb-1 text-gray-900 dark:text-white">Client *</label>
                        <select name="user_id" required
                            class="w-full px-4 py-2.5 border border-gray-200 dark:border-gray-600 rounded-lg focus:border-orange-500 focus:ring-2 focus:ring-orange-100 dark:focus:ring-orange-900 outline-none transition bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                            <option value="">Sélectionnez un client</option>
                            @foreach ($users as $userOption)
                                <option value="{{ $userOption->id }}" {{ old('user_id') == $userOption->id ? 'selected' : '' }}>
                                    {{ $userOption->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('user_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block font-semibold text-sm mb-1 text-gray-900 dark:text-white">Téléphone *</label>
                        <input type="text" name="telephone" value="{{ old('telephone') }}" required
                            class="w-full px-4 py-2.5 border border-gray-200 dark:border-gray-600 rounded-lg focus:border-orange-500 focus:ring-2 focus:ring-orange-100 dark:focus:ring-orange-900 outline-none transition bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                        @error('telephone') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block font-semibold text-sm mb-1 text-gray-900 dark:text-white">Adresse</label>
                        <input type="text" name="adresse" value="{{ old('adresse') }}"
                            class="w-full px-4 py-2.5 border border-gray-200 dark:border-gray-600 rounded-lg focus:border-orange-500 focus:ring-2 focus:ring-orange-100 dark:focus:ring-orange-900 outline-none transition bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                        @error('adresse') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

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

                    <div>
                        <label class="block font-semibold text-sm mb-1 text-gray-900 dark:text-white">Notes</label>
                        <textarea name="notes" rows="4"
                            class="w-full px-4 py-2.5 border border-gray-200 dark:border-gray-600 rounded-lg focus:border-orange-500 focus:ring-2 focus:ring-orange-100 dark:focus:ring-orange-900 outline-none transition bg-white dark:bg-gray-700 text-gray-900 dark:text-white">{{ old('notes') }}</textarea>
                        @error('notes') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            <div class="flex gap-3">
                <button type="submit" class="flex-1 bg-orange-500 hover:bg-orange-600 text-white py-3 rounded-xl font-semibold transition shadow-md hover:shadow-lg">
                    Créer le client
                </button>
                <a href="{{ route('artisan.clients.index') }}" class="px-6 py-3 border border-gray-200 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-xl font-semibold hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                    Annuler
                </a>
            </div>
        </form>
    </div>
</div>
@endsection