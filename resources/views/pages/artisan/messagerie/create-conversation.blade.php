@extends('layouts.artisan')

@section('title', 'Nouvelle conversation - Artisan')

@section('breadcrumbs')
    <nav class="flex items-center gap-2 text-sm text-muted-foreground">
        <a href="{{ route('artisan.messagerie.index') }}" class="transition-colors hover:text-primary">
            <i data-lucide="message-circle" class="h-4 w-4"></i>
            <span>Messagerie</span>
        </a>
        <i data-lucide="chevron-right" class="h-4 w-4"></i>
        <span>Nouvelle conversation</span>
    </nav>
@endsection

@section('content')
<div class="container mx-auto max-w-3xl px-4 py-6 sm:py-8">
    <div class="mb-6">
        <div class="mb-2 flex items-center gap-2 text-xs font-semibold uppercase tracking-[0.18em] text-primary">
            <i data-lucide="message-square-plus" class="h-4 w-4"></i>
            Nouvelle discussion
        </div>
        <h1 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white sm:text-3xl">Nouvelle conversation</h1>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Choisissez un client et donnez un contexte clair à votre échange.</p>
    </div>

    <div class="rounded-3xl border border-gray-200 bg-white p-5 shadow-sm shadow-gray-200/40 ring-1 ring-gray-100 dark:border-gray-700 dark:bg-gray-800 dark:ring-gray-700/70 sm:p-6">
        <form method="POST" action="{{ route('artisan.messagerie.conversation.store') }}" class="space-y-5">
            @csrf

            <div class="space-y-4">
                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Client <span class="text-primary">*</span></label>
                    <select name="client_id" required class="w-full rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-gray-900 shadow-sm transition focus:border-primary focus:ring-2 focus:ring-primary/10 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                        <option value="">Sélectionnez un client</option>
                        @foreach ($clients as $client)
                            <option value="{{ $client->id }}" {{ old('client_id') == $client->id ? 'selected' : '' }}>
                                {{ $client->nom }}
                            </option>
                        @endforeach
                    </select>
                    @error('client_id') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    @if($clients->isEmpty())
                        <p class="mt-2 text-xs text-amber-600 dark:text-amber-400">
                            Aucun client lié à un utilisateur trouvé.
                            <a href="{{ route('artisan.clients.create') }}" class="font-medium underline hover:text-primary">Créez d'abord un client</a>
                            en sélectionnant un utilisateur existant.
                        </p>
                    @endif
                </div>

                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Sujet <span class="font-normal text-gray-400">(optionnel)</span></label>
                    <input type="text" name="sujet" value="{{ old('sujet') }}" class="w-full rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-gray-900 shadow-sm transition focus:border-primary focus:ring-2 focus:ring-primary/10 dark:border-gray-600 dark:bg-gray-700 dark:text-white" placeholder="Ex: Demande de devis, Suivi de chantier...">
                    @error('sujet') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="flex flex-col-reverse gap-3 border-t border-gray-200 pt-5 dark:border-gray-700 sm:flex-row sm:justify-end">
                <a href="{{ route('artisan.messagerie.index') }}" class="inline-flex items-center justify-center rounded-xl border border-gray-200 px-6 py-3 font-semibold text-gray-700 transition hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700">Annuler</a>
                <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-primary px-6 py-3 font-semibold text-white shadow-sm shadow-primary/20 transition hover:bg-primary/90">Créer la conversation</button>
            </div>
        </form>
    </div>
</div>
@endsection