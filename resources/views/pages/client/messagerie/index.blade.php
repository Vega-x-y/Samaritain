@extends('layouts.base')

@section('title', 'Messagerie - Client')

@section('content')
    <x-ui.user-dashboard-nav />
    <x-blade-components::layout.container>
        <div class="container mx-auto px-4 py-8">
            <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-3 mb-6">
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Messagerie</h1>
                <a href="{{ route('client.messagerie.create') }}" class="w-full sm:w-auto justify-center bg-orange-500 hover:bg-orange-600 text-white px-4 py-2 rounded-lg font-medium transition flex items-center gap-2">
                    <i data-lucide="plus" class="w-4 h-4"></i>
                    Nouveau message
                </a>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                @if ($conversations->isNotEmpty())
                    <div class="px-6 py-3 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
                        <h2 class="font-semibold text-gray-900 dark:text-white">💬 Conversations</h2>
                        <form method="POST" action="{{ route('client.messagerie.destroy-all') }}" onsubmit="return confirm('Supprimer toutes les conversations ?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-xs text-red-500 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300">
                                Tout supprimer
                            </button>
                        </form>
                    </div>
                @endif
                @forelse ($conversations as $conversation)
                    <div class="flex items-center group">
                        <a href="{{ route('client.messagerie.show', $conversation) }}" class="block flex-1 px-6 py-4 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition border-b border-gray-100 dark:border-gray-700 last:border-b-0">
                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 rounded-full bg-orange-100 dark:bg-orange-900/30 flex items-center justify-center text-lg font-bold text-orange-600 dark:text-orange-400">
                                    {{ substr($conversation->artisan->business_name ?? 'A', 0, 2) }}
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex justify-between items-center">
                                        <h3 class="font-semibold text-gray-900 dark:text-white truncate">{{ $conversation->artisan->business_name ?? 'Artisan' }}</h3>
                                        <span class="text-xs text-gray-500 dark:text-gray-400">{{ $conversation->dernier_message_at?->format('d/m/Y H:i') }}</span>
                                    </div>
                                    @if ($conversation->sujet)
                                        <p class="text-sm text-gray-600 dark:text-gray-300 mt-0.5">{{ $conversation->sujet }}</p>
                                    @endif
                                    @if ($conversation->messages->first())
                                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1 truncate">{{ $conversation->messages->first()->contenu ?? 'Fichier joint' }}</p>
                                    @endif
                                </div>
                            </div>
                        </a>
                        <form method="POST" action="{{ route('client.messagerie.destroy', $conversation) }}" onsubmit="return confirm('Supprimer cette conversation ?')" class="mr-3">
                            @csrf @method('DELETE')
                            <button type="submit" aria-label="Supprimer la conversation" class="text-gray-400 hover:text-red-500 dark:text-gray-500 dark:hover:text-red-400 p-2 rounded-lg hover:bg-red-50 dark:hover:bg-red-900/20 transition opacity-100 sm:opacity-0 sm:group-hover:opacity-100">
                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                            </button>
                        </form>
                    </div>
                @empty
                    <div class="px-6 py-12 text-center">
                        <i data-lucide="message-circle" class="w-12 h-12 text-gray-400 mx-auto mb-3"></i>
                        <p class="text-gray-500 dark:text-gray-400">Aucune conversation pour le moment.</p>
                        <a href="{{ route('client.messagerie.create') }}" class="text-orange-500 hover:text-orange-600 text-sm font-medium mt-2 inline-block">Démarrer une conversation</a>
                    </div>
                @endforelse
            </div>
        </div>
    </x-blade-components::layout.container>
@endsection