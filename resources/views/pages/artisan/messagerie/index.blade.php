@extends('layouts.artisan')

@section('title', 'Messagerie - Artisan')

@section('breadcrumbs')
    <nav class="flex items-center gap-2 text-sm text-muted-foreground">
        <i data-lucide="message-circle" class="w-4 h-4"></i>
        <span>Messagerie</span>
    </nav>
@endsection

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-8">
        <div>
            <h1 class="text-xl md:text-2xl font-bold text-gray-900 dark:text-white">Ma <span class="text-orange-500">messagerie</span></h1>
            <p class="text-gray-500 dark:text-gray-400 mt-1 text-sm">Conversations</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('artisan.messagerie.conversation.create') }}" class="bg-orange-500 hover:bg-orange-600 text-white px-5 py-2.5 rounded-full font-semibold text-sm transition shadow-md hover:shadow-lg">
                + Nouvelle conversation
            </a>
        </div>
    </div>
    <!-- Barre de recherche -->
    <div class="mb-4">
        @include('components.artisan.search-bar', ['placeholder' => 'Rechercher un message…'])
    </div>

    <div class="grid grid-cols-1 gap-6">

    <div class="grid grid-cols-1 gap-6">
        <!-- Liste des conversations -->
        <div class="space-y-6">
            <div class="bg-sidebar dark:bg-gray-800 rounded-xl border border-accent dark:border-gray-700 shadow-sm overflow-hidden">
                <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
                    <h2 class="font-semibold text-gray-900 dark:text-white">💬 Conversations</h2>
                    @if ($conversations->isNotEmpty())
                        <form method="POST" action="{{ route('artisan.messagerie.conversation.destroy-all') }}" onsubmit="return confirm('Supprimer toutes les conversations ?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-xs text-red-500 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300">
                                Tout supprimer
                            </button>
                        </form>
                    @endif
                </div>
                <div class="divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse ($conversations as $conversation)
                        <div class="flex items-center group">
                            <a href="{{ route('artisan.messagerie.conversation', $conversation) }}" 
                                class="block flex-1 px-4 py-3 hover:bg-gray-50 dark:hover:bg-gray-700 transition {{ $conversation->lu ? '' : 'bg-orange-50 dark:bg-orange-900/10' }}">
                                <div class="flex items-start gap-3">
                                    <div class="w-10 h-10 rounded-full bg-orange-100 dark:bg-orange-900/30 flex items-center justify-center text-sm font-bold text-orange-600 dark:text-orange-400 shrink-0">
                                        {{ substr($conversation->participant_name, 0, 2) }}
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center justify-between">
                                            <span class="font-medium text-sm text-gray-900 dark:text-white truncate">{{ $conversation->participant_name }}</span>
                                            @if ($conversation->dernier_message_at)
                                                <span class="text-xs text-gray-500 dark:text-gray-400">{{ $conversation->dernier_message_at->format('d/m/Y H:i') }}</span>
                                            @endif
                                        </div>
                                        @if ($conversation->sujet)
                                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">{{ $conversation->sujet }}</p>
                                        @endif
                                    </div>
                                </div>
                            </a>
                            <form method="POST" action="{{ route('artisan.messagerie.conversation.destroy', $conversation) }}" onsubmit="return confirm('Supprimer cette conversation ?')" class="mr-2">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-gray-400 hover:text-red-500 dark:text-gray-500 dark:hover:text-red-400 p-2 rounded-lg hover:bg-red-50 dark:hover:bg-red-900/20 transition opacity-0 group-hover:opacity-100">
                                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                                </button>
                            </form>
                        </div>
                    @empty
                        <div class="px-4 py-8 text-center text-sm text-gray-500 dark:text-gray-400">
                            Aucune conversation
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
