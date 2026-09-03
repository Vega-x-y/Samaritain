@extends('layouts.artisan')

@section('title', 'Nouvelle conversation - Artisan')

@section('breadcrumbs')
    <nav class="flex items-center gap-2 text-sm text-muted-foreground">
        <a href="{{ route('artisan.messagerie.index') }}" class="hover:text-primary transition-colors">
            <i data-lucide="message-circle" class="w-4 h-4"></i>
            <span>Messagerie</span>
        </a>
        <i data-lucide="chevron-right" class="w-4 h-4"></i>
        <span>Nouvelle conversation</span>
    </nav>
@endsection

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="mb-8">
        <h1 class="text-xl md:text-2xl font-bold text-gray-900 dark:text-white"><i data-lucide="wallet" class="w-4 h-4 inline-block align-middle mr-1"></i> Nouvelle <span class="text-primary">conversation</span></h1>
        <p class="text-gray-500 dark:text-gray-400 mt-1 text-sm">Créez une conversation avec un client</p>
    </div>

    <div class="max-w-3xl">
        <form method="POST" action="{{ route('artisan.messagerie.conversation.store') }}" class="space-y-6">
            @csrf

            <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-sm border border-gray-100 dark:border-gray-700">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Informations</h2>

                <div class="space-y-4">
                    <div>
                        <label class="block font-semibold text-sm mb-1 text-gray-900 dark:text-white">Client *</label>
                        <select name="client_id" required
                            class="w-full px-4 py-2.5 border border-gray-200 dark:border-gray-600 rounded-lg focus:border-primary focus:ring-2 focus:ring-primary/10 dark:focus:ring-primary/20 outline-none transition bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                            <option value="">Sélectionnez un client</option>
                            @foreach ($clients as $client)
                                <option value="{{ $client->id }}" {{ old('client_id') == $client->id ? 'selected' : '' }}>
                                    {{ $client->nom }}
                                </option>
                            @endforeach
                        </select>
                        @error('client_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        @if($clients->isEmpty())
                            <p class="text-amber-600 dark:text-amber-400 text-xs mt-2">
                                Aucun client lié à un utilisateur trouvé. 
                                <a href="{{ route('artisan.clients.create') }}" class="underline hover:text-primary">Créez d'abord un client</a>
                                en sélectionnant un utilisateur existant.
                            </p>
                        @endif
                    </div>

                    <div>
                        <label class="block font-semibold text-sm mb-1 text-gray-900 dark:text-white">Sujet (optionnel)</label>
                        <input type="text" name="sujet" value="{{ old('sujet') }}"
                            class="w-full px-4 py-2.5 border border-gray-200 dark:border-gray-600 rounded-lg focus:border-primary focus:ring-2 focus:ring-primary/10 dark:focus:ring-primary/20 outline-none transition bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                            placeholder="Ex: Demande de devis, Suivi de chantier...">
                        @error('sujet') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            <div class="flex gap-3">
                <button type="submit" class="flex-1 bg-primary hover:bg-primary/90 text-white py-3 rounded-xl font-semibold transition shadow-md">
                    Créer la conversation
                </button>
                <a href="{{ route('artisan.messagerie.index') }}" class="px-6 py-3 border border-gray-200 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-xl font-semibold hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                    Annuler
                </a>
            </div>
        </form>
    </div>
</div>
@endsection