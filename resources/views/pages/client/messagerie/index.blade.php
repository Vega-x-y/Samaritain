@extends('layouts.base')

@section('title', 'Messagerie - Client')

@section('content')
    <x-ui.user-dashboard-nav />
    <x-blade-components::layout.container>
        <div class="container mx-auto max-w-6xl px-4 py-6 sm:py-8">
            <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <div class="mb-2 flex items-center gap-2 text-xs font-semibold uppercase tracking-[0.18em] text-orange-500">
                        <i data-lucide="messages-square" class="h-4 w-4"></i>
                        Espace d'échange
                    </div>
                    <h1 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white sm:text-3xl">Messagerie</h1>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Retrouvez votre historique avec les artisans et répondez rapidement.</p>
                </div>
                <a href="{{ route('client.messagerie.create') }}" class="flex w-full items-center justify-center gap-2 rounded-xl bg-orange-500 px-4 py-2.5 font-medium text-white shadow-sm shadow-orange-200/60 transition hover:bg-orange-600 sm:w-auto">
                    <i data-lucide="plus" class="h-4 w-4"></i>
                    Nouveau message
                </a>
            </div>

            <div class="mb-5 grid gap-3 sm:grid-cols-2">
                <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm shadow-gray-200/50 dark:border-gray-700 dark:bg-gray-800">
                    <div class="flex items-center justify-between">
                        <p class="text-[11px] uppercase tracking-[0.18em] text-gray-400">Conversations</p>
                        <span class="rounded-xl bg-orange-100 p-2.5 text-orange-600 dark:bg-orange-500/15 dark:text-orange-300"><i data-lucide="inbox" class="h-4 w-4"></i></span>
                    </div>
                    <p class="mt-4 text-2xl font-bold text-gray-900 dark:text-white">{{ $conversations->count() }}</p>
                </div>
                <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm shadow-gray-200/50 dark:border-gray-700 dark:bg-gray-800">
                    <div class="flex items-center justify-between">
                        <p class="text-[11px] uppercase tracking-[0.18em] text-gray-400">Réponses</p>
                        <span class="rounded-xl bg-emerald-100 p-2.5 text-emerald-600 dark:bg-emerald-500/15 dark:text-emerald-300"><i data-lucide="message-square-text" class="h-4 w-4"></i></span>
                    </div>
                    <p class="mt-4 text-2xl font-bold text-gray-900 dark:text-white">{{ $conversations->sum(fn ($conversation) => $conversation->messages->count()) }}</p>
                </div>
            </div>

            <div class="overflow-hidden rounded-3xl border border-gray-200 bg-white shadow-sm shadow-gray-200/50 ring-1 ring-gray-100 dark:border-gray-700 dark:bg-gray-800 dark:ring-gray-700/70">
                @if ($conversations->isNotEmpty())
                    <div class="flex items-center justify-between gap-4 border-b border-gray-200 bg-gradient-to-r from-orange-50 via-orange-0 to-white px-4 py-4 dark:border-gray-700 dark:from-orange-500/10 dark:to-gray-800 sm:px-6">
                        <div>
                            <h2 class="flex items-center gap-2 font-semibold text-gray-900 dark:text-white"><i data-lucide="inbox" class="h-4 w-4 text-orange-500"></i> Conversations</h2>
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ $conversations->count() }} échange(s) actif(s)</p>
                        </div>
                        <form method="POST" action="{{ route('client.messagerie.destroy-all') }}" onsubmit="return confirm('Supprimer toutes les conversations ?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="inline-flex items-center gap-1.5 rounded-lg px-2.5 py-2 text-xs font-medium text-red-500 transition hover:bg-red-50 hover:text-red-700 dark:text-red-400 dark:hover:bg-red-900/20 dark:hover:text-red-300">
                                <i data-lucide="trash-2" class="h-3.5 w-3.5"></i>
                                Tout supprimer
                            </button>
                        </form>
                    </div>
                @endif

                @forelse ($conversations as $conversation)
                    <div class="group flex items-center border-b border-gray-100 bg-white transition last:border-b-0 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:hover:bg-gray-700/60">
                        <a href="{{ route('client.messagerie.show', $conversation) }}" class="block min-w-0 flex-1 px-4 py-4 sm:px-6">
                            <div class="flex items-start gap-3.5">
                                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-orange-100 text-sm font-bold text-orange-600 shadow-sm ring-1 ring-orange-100 dark:bg-orange-900/30 dark:text-orange-400 dark:ring-orange-900/40">
                                    {{ strtoupper(substr($conversation->artisan->business_name ?? 'A', 0, 2)) }}
                                </div>
                                <div class="min-w-0 flex-1">
                                    <div class="flex items-start justify-between gap-3">
                                        <div class="min-w-0 flex-1">
                                            <h3 class="truncate text-sm font-semibold text-gray-900 dark:text-white">{{ $conversation->artisan->business_name ?? 'Artisan' }}</h3>
                                            @if ($conversation->sujet)
                                                <p class="mt-1 truncate text-xs text-gray-500 dark:text-gray-400">{{ $conversation->sujet }}</p>
                                            @endif
                                        </div>
                                        <span class="shrink-0 text-[11px] text-gray-500 dark:text-gray-400">{{ $conversation->dernier_message_at?->format('d/m/Y H:i') }}</span>
                                    </div>
                                    @if ($conversation->messages->first())
                                        <p class="mt-2 truncate text-sm text-gray-500 dark:text-gray-400">{{ $conversation->messages->first()->contenu ?? 'Fichier joint' }}</p>
                                    @endif
                                </div>
                            </div>
                        </a>
                        <form method="POST" action="{{ route('client.messagerie.destroy', $conversation) }}" onsubmit="return confirm('Supprimer cette conversation ?')" class="mr-3">
                            @csrf @method('DELETE')
                            <button type="submit" aria-label="Supprimer la conversation" class="rounded-lg p-2 text-gray-400 transition hover:bg-red-50 hover:text-red-500 dark:text-gray-500 dark:hover:bg-red-900/20 dark:hover:text-red-400 sm:opacity-0 sm:group-hover:opacity-100">
                                <i data-lucide="trash-2" class="h-4 w-4"></i>
                            </button>
                        </form>
                    </div>
                @empty
                    <div class="px-6 py-12 text-center">
                        <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-2xl bg-orange-100 text-orange-600 dark:bg-orange-500/15 dark:text-orange-300">
                            <i data-lucide="message-circle" class="h-7 w-7"></i>
                        </div>
                        <p class="text-gray-500 dark:text-gray-400">Aucune conversation pour le moment.</p>
                        <a href="{{ route('client.messagerie.create') }}" class="mt-3 inline-block text-sm font-medium text-orange-500 hover:text-orange-600">Démarrer une conversation</a>
                    </div>
                @endforelse
            </div>
        </div>
    </x-blade-components::layout.container>
@endsection