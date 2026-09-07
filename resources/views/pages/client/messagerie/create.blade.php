@extends('layouts.base')

@section('title', 'Nouvelle conversation - Messagerie - Client')

@section('content')
    <x-ui.user-dashboard-nav />
    <x-blade-components::layout.container>
        <div class="container mx-auto max-w-3xl px-4 py-6 sm:py-8">
            <div class="mb-6">
                <div class="mb-2 flex items-center gap-2 text-xs font-semibold uppercase tracking-[0.18em] text-orange-500">
                    <i data-lucide="message-square-plus" class="h-4 w-4"></i>
                    Nouvelle discussion
                </div>
                <h1 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white sm:text-3xl">Nouveau message</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Sélectionnez un artisan pour démarrer une conversation.</p>
            </div>

            <div class="rounded-3xl border border-gray-200 bg-white p-5 shadow-sm shadow-gray-200/40 ring-1 ring-gray-100 dark:border-gray-700 dark:bg-gray-800 dark:ring-gray-700/70 sm:p-6">
                <form method="POST" action="{{ route('client.messagerie.store') }}">
                    @csrf

                    <div class="mb-5">
                        <label for="artisan_id" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Artisan <span class="text-orange-500">*</span></label>
                        <select name="artisan_id" id="artisan_id" required class="w-full rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-gray-900 shadow-sm transition focus:border-orange-500 focus:ring-2 focus:ring-orange-100 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                            <option value="">Sélectionner un artisan</option>
                            @foreach ($artisans as $artisan)
                                <option value="{{ $artisan->id }}">{{ $artisan->business_name }} - {{ $artisan->profession }}</option>
                            @endforeach
                        </select>
                        @error('artisan_id')
                            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mb-6">
                        <label for="sujet" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Sujet <span class="font-normal text-gray-400">(optionnel)</span></label>
                        <input type="text" name="sujet" id="sujet" maxlength="255" class="w-full rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-gray-900 shadow-sm transition focus:border-orange-500 focus:ring-2 focus:ring-orange-100 dark:border-gray-600 dark:bg-gray-700 dark:text-white" placeholder="Ex: Demande de devis">
                        @error('sujet')
                            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex flex-col-reverse gap-3 border-t border-gray-200 pt-5 dark:border-gray-700 sm:flex-row sm:justify-end">
                        <a href="{{ route('client.messagerie.index') }}" class="inline-flex items-center justify-center rounded-xl bg-gray-100 px-6 py-2.5 font-medium text-gray-700 transition hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600">Annuler</a>
                        <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-orange-500 px-6 py-2.5 font-medium text-white shadow-sm shadow-orange-200/60 transition hover:bg-orange-600">Créer la conversation</button>
                    </div>
                </form>
            </div>
        </div>
    </x-blade-components::layout.container>
@endsection