@extends('layouts.artisan')

@section('title', 'Modifier document - Artisan')

@section('breadcrumbs')
    <nav class="flex items-center gap-2 text-sm text-muted-foreground">
        <a href="{{ route('artisan.documents.index') }}" class="hover:text-primary transition-colors">
            <i data-lucide="file-text" class="w-4 h-4"></i>
            <span>Documents</span>
        </a>
        <i data-lucide="chevron-right" class="w-4 h-4"></i>
        <span>Modifier document</span>
    </nav>
@endsection

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- En-tête -->
    <div class="mb-8">
        <h1 class="text-xl md:text-2xl font-bold text-gray-900 dark:text-white"><i data-lucide="file-pen" class="w-4 h-4 inline-block align-middle mr-1"></i>Modifier le <span class="text-primary">document</span></h1>
        <p class="text-gray-500 dark:text-gray-400 mt-1 text-sm">Modifiez les informations du document</p>
    </div>

    <div class="max-w-3xl">
        <form method="POST" action="{{ route('artisan.documents.update', $document) }}" class="space-y-6">
            @csrf
            @method('PUT')

            <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-sm border border-gray-100 dark:border-gray-700">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Informations du document</h2>

                <div class="space-y-4">
                    <div>
                        <label class="block font-semibold text-sm mb-1 text-gray-900 dark:text-white">Nom du document *</label>
                        <input type="text" name="nom" value="{{ old('nom', $document->nom) }}" required
                            class="w-full px-4 py-2.5 border border-gray-200 dark:border-gray-600 rounded-lg focus:border-primary focus:ring-2 focus:ring-primary/10 dark:focus:ring-primary/20 outline-none transition bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                        @error('nom') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block font-semibold text-sm mb-1 text-gray-900 dark:text-white">Client *</label>
                        <select name="client_id" required
                            class="w-full px-4 py-2.5 border border-gray-200 dark:border-gray-600 rounded-lg focus:border-primary focus:ring-2 focus:ring-primary/10 dark:focus:ring-primary/20 outline-none transition bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                            <option value="">Sélectionnez un client</option>
                            @foreach ($clients as $client)
                                <option value="{{ $client->id }}" {{ old('client_id', $document->client_id) == $client->id ? 'selected' : '' }}>{{ $client->nom }}</option>
                            @endforeach
                        </select>
                        @error('client_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block font-semibold text-sm mb-1 text-gray-900 dark:text-white">Type de document *</label>
                        <select name="type" required
                            class="w-full px-4 py-2.5 border border-gray-200 dark:border-gray-600 rounded-lg focus:border-primary focus:ring-2 focus:ring-primary/10 dark:focus:ring-primary/20 outline-none transition bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                            @foreach ($types as $value => $label)
                                <option value="{{ $value }}" {{ old('type', $document->type) === $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('type') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block font-semibold text-sm mb-1 text-gray-900 dark:text-white">Chantier associé (optionnel)</label>
                        <select name="chantier_id"
                            class="w-full px-4 py-2.5 border border-gray-200 dark:border-gray-600 rounded-lg focus:border-primary focus:ring-2 focus:ring-primary/10 dark:focus:ring-primary/20 outline-none transition bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                            <option value="">-- Aucun chantier --</option>
                            @foreach ($chantiers as $chantier)
                                <option value="{{ $chantier->id }}" {{ old('chantier_id', $document->chantier_id) == $chantier->id ? 'selected' : '' }}>{{ $chantier->nom }}</option>
                            @endforeach
                        </select>
                        @error('chantier_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    @if($document->date_modification)
                    <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
                        <p class="text-sm text-blue-800 dark:text-blue-300">
                            <strong>Dernière modification :</strong> {{ $document->formatted_date_modification }}
                        </p>
                    </div>
                    @endif

                    <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
                        <p class="text-sm text-blue-800 dark:text-blue-300">
                            <strong>Fichier actuel :</strong> {{ $document->nom }}
                            <br>
                            <span class="text-xs">Pour remplacer le fichier, supprimez ce document et créez-en un nouveau.</span>
                        </p>
                    </div>
                </div>
            </div>

            <div class="flex gap-3">
                <button type="submit" class="flex-1 bg-primary hover:bg-primary/90 text-white py-3 rounded-xl font-semibold transition shadow-md">
                    Mettre à jour
                </button>
                <a href="{{ route('artisan.documents.index') }}" class="px-6 py-3 border border-gray-200 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-xl font-semibold hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                    Annuler
                </a>
            </div>
        </form>
    </div>
</div>
@endsection