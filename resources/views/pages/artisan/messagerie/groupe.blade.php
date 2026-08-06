@extends('layouts.artisan')

@section('title', $groupe->nom.' - Messagerie - Artisan')

@section('breadcrumbs')
    <nav class="flex items-center gap-2 text-sm text-muted-foreground">
        <a href="{{ route('artisan.messagerie.index') }}" class="hover:text-foreground transition-colors flex items-center gap-1">
            <i data-lucide="message-circle" class="w-4 h-4"></i>
            <span>Messagerie</span>
        </a>
        <span class="text-muted-foreground">/</span>
        <span class="text-foreground font-medium truncate max-w-48">{{ $groupe->nom }}</span>
    </nav>
@endsection

@section('content')
<div class="container mx-auto px-4 py-8" x-data="groupeApp({{ $groupe->id }})">
    <div class="flex flex-col h-[calc(100vh-200px)]">
        <!-- En-tête du groupe -->
        <div class="flex items-center gap-4 mb-4 pb-4 border-b border-gray-200 dark:border-gray-700">
            <div class="w-12 h-12 rounded-full bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center text-lg font-bold text-blue-600 dark:text-blue-400">
                👥
            </div>
            <div>
                <h1 class="text-lg font-bold text-gray-900 dark:text-white">{{ $groupe->nom }}</h1>
                @if ($groupe->description)
                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ $groupe->description }}</p>
                @endif
            </div>
        </div>

        <!-- Messages -->
        <div class="flex-1 overflow-y-auto mb-4 space-y-3" id="messages-container">
            @foreach ($groupe->messages as $message)
                <div class="flex {{ $message->expediteur_type === 'artisan' ? 'justify-end' : 'justify-start' }}">
                    <div class="max-w-[70%] {{ $message->expediteur_type === 'artisan' ? 'bg-orange-500 text-white' : 'bg-gray-200 dark:bg-gray-700 text-gray-900 dark:text-white' }} rounded-lg px-4 py-2">
                        <div class="text-xs font-medium mb-1 {{ $message->expediteur_type === 'artisan' ? 'text-orange-100' : 'text-gray-500 dark:text-gray-400' }}">
                            {{ $message->expediteur_nom }}
                        </div>
                        <div class="text-sm whitespace-pre-wrap">{{ $message->contenu }}</div>
                        <div class="text-xs mt-1 {{ $message->expediteur_type === 'artisan' ? 'text-orange-100' : 'text-gray-500 dark:text-gray-400' }}">
                            {{ $message->created_at->format('d/m/Y H:i') }}
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Formulaire d'envoi -->
        <form method="POST" action="{{ route('artisan.messagerie.groupes.message', $groupe) }}" class="flex gap-2">
            @csrf
            <input type="text" name="contenu" required placeholder="Écrivez votre message..."
                class="flex-1 px-4 py-2.5 border border-gray-200 dark:border-gray-600 rounded-lg focus:border-orange-500 focus:ring-2 focus:ring-orange-100 dark:focus:ring-orange-900 outline-none transition bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
            <button type="submit" class="bg-orange-500 hover:bg-orange-600 text-white px-6 py-2.5 rounded-lg font-medium transition">
                Envoyer
            </button>
        </form>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('groupeApp', (groupeId) => ({
            pollingInterval: null,

            init() {
                this.pollingInterval = setInterval(() => {
                    this.refreshMessages();
                }, 5000);
            },

            refreshMessages() {
                fetch(`/artisan/messagerie/groupes/${groupeId}?ajax=1`)
                    .then(response => response.text())
                    .then(html => {
                        const parser = new DOMParser();
                        const doc = parser.parseFromString(html, 'text/html');
                        const newMessages = doc.querySelector('#messages-container');
                        if (newMessages) {
                            document.getElementById('messages-container').innerHTML = newMessages.innerHTML;
                        }
                    })
                    .catch(error => console.error('Erreur polling:', error));
            },

            destroy() {
                if (this.pollingInterval) {
                    clearInterval(this.pollingInterval);
                }
            }
        }));
    });
</script>
@endpush
@endsection