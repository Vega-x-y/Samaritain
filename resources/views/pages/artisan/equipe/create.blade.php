@extends('layouts.artisan')

@section('title', 'Nouveau membre - Artisan')

@section('breadcrumbs')
    <nav class="flex items-center gap-2 text-sm text-muted-foreground">
        <a href="{{ route('artisan.equipe.index') }}" class="hover:text-orange-500 transition-colors">
            <i data-lucide="users" class="w-4 h-4"></i>
            <span>Mon équipe</span>
        </a>
        <i data-lucide="chevron-right" class="w-4 h-4"></i>
        <span>Nouveau membre</span>
    </nav>
@endsection

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- En-tête -->
    <div class="mb-8">
        <h1 class="text-xl md:text-2xl font-bold text-gray-900 dark:text-white">👷 Nouveau membre d'<span class="text-orange-500">équipe</span></h1>
        <p class="text-gray-500 dark:text-gray-400 mt-1 text-sm">Ajoutez un nouveau membre à votre équipe</p>
    </div>

    <div class="max-w-3xl">
        <form method="POST" action="{{ route('artisan.equipe.store') }}" class="space-y-6">
            @csrf

            <div class="bg-sidebar dark:bg-gray-800 rounded-xl p-6 shadow-sm border border-accent dark:border-gray-700">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Informations du membre</h2>

                <div class="space-y-4">
                    <div>
                        <label class="block font-semibold text-sm mb-1 text-gray-900 dark:text-white">Nom complet *</label>
                        <input type="text" name="nom" value="{{ old('nom') }}" required
                            class="w-full px-4 py-2.5 border border-gray-200 dark:border-gray-600 rounded-lg focus:border-orange-500 focus:ring-2 focus:ring-orange-100 dark:focus:ring-orange-900 outline-none transition bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                        @error('nom') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block font-semibold text-sm mb-1 text-gray-900 dark:text-white">Rôle *</label>
                        <input type="text" name="role" value="{{ old('role') }}" required placeholder="Ex: Maçon, Électricien, Peintre..."
                            class="w-full px-4 py-2.5 border border-gray-200 dark:border-gray-600 rounded-lg focus:border-orange-500 focus:ring-2 focus:ring-orange-100 dark:focus:ring-orange-900 outline-none transition bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                        @error('role') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
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
                        <label class="block font-semibold text-sm mb-1 text-gray-900 dark:text-white">Statut *</label>
                        <select name="statut" required
                            class="w-full px-4 py-2.5 border border-gray-200 dark:border-gray-600 rounded-lg focus:border-orange-500 focus:ring-2 focus:ring-orange-100 dark:focus:ring-orange-900 outline-none transition bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                            @foreach ($statuts as $statut)
                                <option value="{{ $statut->value }}" {{ old('statut') === $statut->value ? 'selected' : '' }}>
                                    {{ $statut->label() }}
                                </option>
                            @endforeach
                        </select>
                        @error('statut') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            <div class="flex gap-3">
                <button type="submit" class="flex-1 bg-orange-500 hover:bg-orange-600 text-white py-3 rounded-xl font-semibold transition shadow-md hover:shadow-lg">
                    Ajouter le membre
                </button>
                <a href="{{ route('artisan.equipe.index') }}" class="px-6 py-3 border border-gray-200 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-xl font-semibold hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                    Annuler
                </a>
            </div>
        </form>
    </div>
</div>
@endsection