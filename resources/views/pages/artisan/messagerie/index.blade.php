@extends('layouts.artisan')

@section('title', 'Messagerie - Artisan')

@section('breadcrumbs')
    <nav class="flex items-center gap-2 text-sm text-muted-foreground">
        <i data-lucide="message-circle" class="h-4 w-4"></i>
        <span>Messagerie</span>
    </nav>
@endsection

@section('content')
<div class="container mx-auto max-w-6xl px-4 py-6 sm:py-8">
    <div class="mb-6 flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <div class="mb-2 flex items-center gap-2 text-xs font-semibold uppercase tracking-[0.18em] text-primary">
                <i data-lucide="messages-square" class="h-4 w-4"></i>
                Espace d'échange
            </div>
            <h1 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white sm:text-3xl">Ma messagerie</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Suivez vos échanges clients, gérez les demandes et gardez chaque dossier sous contrôle.</p>
        </div>

        <x-btn href="{{ route('artisan.messagerie.conversation.create') }}" size="lg" class="w-full sm:w-auto">
            <x-slot:prefix><i data-lucide="plus" class="h-4 w-4"></i></x-slot:prefix>
            Nouvelle conversation
        </x-btn>
    </div>

    <div class="mb-5 grid gap-3 sm:grid-cols-3">
        <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm shadow-gray-200/50 dark:border-gray-700 dark:bg-gray-800">
            <div class="flex items-center justify-between">
                <p class="text-[11px] uppercase tracking-[0.18em] text-gray-400">Total</p>
                <span class="rounded-xl bg-primary/10 p-2.5 text-primary dark:bg-primary/20"><i data-lucide="inbox" class="h-4 w-4"></i></span>
            </div>
            <p class="mt-4 text-2xl font-bold text-gray-900 dark:text-white">{{ $conversations->count() }}</p>
        </div>
        <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm shadow-gray-200/50 dark:border-gray-700 dark:bg-gray-800">
            <div class="flex items-center justify-between">
                <p class="text-[11px] uppercase tracking-[0.18em] text-gray-400">Non lus</p>
                <span class="rounded-xl bg-amber-100 p-2.5 text-amber-600 dark:bg-amber-500/15 dark:text-amber-300"><i data-lucide="mail" class="h-4 w-4"></i></span>
            </div>
            <p class="mt-4 text-2xl font-bold text-gray-900 dark:text-white">{{ $conversations->where('lu', false)->count() }}</p>
        </div>
        <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm shadow-gray-200/50 dark:border-gray-700 dark:bg-gray-800">
            <div class="flex items-center justify-between">
                <p class="text-[11px] uppercase tracking-[0.18em] text-gray-400">Groupes</p>
                <span class="rounded-xl bg-sky-100 p-2.5 text-sky-600 dark:bg-sky-500/15 dark:text-sky-300"><i data-lucide="users" class="h-4 w-4"></i></span>
            </div>
            <p class="mt-4 text-2xl font-bold text-gray-900 dark:text-white">{{ $groupes->count() }}</p>
        </div>
    </div>

    <div class="mb-5 rounded-2xl border border-gray-200 bg-white p-3 shadow-sm shadow-gray-200/40 dark:border-gray-700 dark:bg-gray-800 sm:p-4">
        @include('components.artisan.search-bar', ['placeholder' => 'Rechercher une conversation'])
    </div>

    <div class="overflow-hidden rounded-3xl border border-gray-200 bg-white shadow-sm shadow-gray-200/50 ring-1 ring-gray-100 dark:border-gray-700 dark:bg-gray-800 dark:ring-gray-700/70">
        <div class="flex items-center justify-between gap-4 border-b border-gray-200 bg-gradient-to-r from-primary/5 via-primary/0 to-transparent px-4 py-4 dark:border-gray-700 sm:px-6">
            <div>
                <h2 class="flex items-center gap-2 font-semibold text-gray-900 dark:text-white">
                    <i data-lucide="inbox" class="h-4 w-4 text-primary"></i>
                    Conversations
                </h2>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ $conversations->count() }} échange(s) actif(s)</p>
            </div>
            @if ($conversations->isNotEmpty())
                <form method="POST" action="{{ route('artisan.messagerie.conversation.destroy-all') }}" onsubmit="return confirm('Supprimer toutes les conversations ?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="inline-flex items-center gap-1.5 rounded-lg px-2.5 py-2 text-xs font-medium text-red-500 transition hover:bg-red-50 hover:text-red-700 dark:text-red-400 dark:hover:bg-red-900/20 dark:hover:text-red-300">
                        <i data-lucide="trash-2" class="h-3.5 w-3.5"></i>
                        Tout supprimer
                    </button>
                </form>
            @endif
        </div>

        <div class="divide-y divide-gray-100 dark:divide-gray-700">
            @forelse ($conversations as $conversation)
                <div class="group flex items-center transition {{ $conversation->lu ? 'bg-white hover:bg-gray-50 dark:bg-gray-800 dark:hover:bg-gray-700/60' : 'bg-primary/[0.04] hover:bg-primary/[0.06] dark:bg-primary/[0.08] dark:hover:bg-primary/[0.12]' }}">
                    <a href="{{ route('artisan.messagerie.conversation', $conversation) }}" class="block min-w-0 flex-1 px-4 py-4 sm:px-6">
                        <div class="flex items-start gap-3.5">
                            <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-primary/10 text-sm font-bold text-primary shadow-sm ring-1 ring-primary/10 dark:bg-primary/20 dark:ring-primary/20">
                                {{ strtoupper(substr($conversation->participant_name, 0, 2)) }}
                            </div>
                            <div class="min-w-0 flex-1">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="min-w-0 flex-1">
                                        <span class="truncate text-sm font-semibold text-gray-900 dark:text-white">{{ $conversation->participant_name }}</span>
                                        @if ($conversation->sujet)
                                            <p class="mt-1 truncate text-xs text-gray-500 dark:text-gray-400">{{ $conversation->sujet }}</p>
                                        @endif
                                    </div>
                                    @if ($conversation->dernier_message_at)
                                        <span class="shrink-0 text-[11px] text-gray-500 dark:text-gray-400">{{ $conversation->dernier_message_at->format('d/m/Y H:i') }}</span>
                                    @endif
                                </div>
                                @unless ($conversation->lu)
                                    <span class="mt-2 inline-flex items-center gap-1.5 text-[11px] font-semibold text-primary">
                                        <span class="h-1.5 w-1.5 rounded-full bg-primary"></span>
                                        Non lu
                                    </span>
                                @endunless
                            </div>
                        </div>
                    </a>
                    <form method="POST" action="{{ route('artisan.messagerie.conversation.destroy', $conversation) }}" onsubmit="return confirm('Supprimer cette conversation ?')" class="mr-3">
                        @csrf @method('DELETE')
                        <button type="submit" aria-label="Supprimer la conversation" class="rounded-lg p-2 text-gray-400 transition hover:bg-red-50 hover:text-red-500 dark:text-gray-500 dark:hover:bg-red-900/20 dark:hover:text-red-400 sm:opacity-0 sm:group-hover:opacity-100">
                            <i data-lucide="trash-2" class="h-4 w-4"></i>
                        </button>
                    </form>
                </div>
            @empty
                <div class="px-6 py-14 text-center">
                    <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-2xl bg-primary/10 text-primary dark:bg-primary/20">
                        <i data-lucide="messages-square" class="h-7 w-7"></i>
                    </div>
                    <p class="font-medium text-gray-700 dark:text-gray-200">Votre boîte est vide</p>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Démarrez une conversation avec un client pour lancer un échange.</p>
                </div>
            @endforelse
        </div>
    </div>
</div>
@endsection
