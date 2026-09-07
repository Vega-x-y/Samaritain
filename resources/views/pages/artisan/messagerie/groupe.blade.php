@extends('layouts.artisan')

@section('title', $groupe->nom.' - Messagerie - Artisan')

@section('breadcrumbs')
    <nav class="flex items-center gap-2 text-sm text-muted-foreground">
        <a href="{{ route('artisan.messagerie.index') }}" class="flex items-center gap-1 transition-colors hover:text-foreground">
            <i data-lucide="message-circle" class="h-4 w-4"></i>
            <span>Messagerie</span>
        </a>
        <span class="text-muted-foreground">/</span>
        <span class="max-w-48 truncate font-medium text-foreground">{{ $groupe->nom }}</span>
    </nav>
@endsection

@section('content')
<div class="container mx-auto max-w-5xl px-4 py-8" x-data="groupeApp({{ $groupe->id }})">
    <div class="flex h-[calc(100vh-200px)] flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white shadow-sm ring-1 ring-gray-100 dark:border-gray-700 dark:bg-gray-800 dark:ring-gray-700/70">
        <div class="flex items-center gap-4 border-b border-gray-200 bg-gradient-to-r from-sky-50 to-white px-4 py-4 dark:border-gray-700 dark:from-sky-500/10 dark:to-gray-800 sm:px-6">
            <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-sky-100 text-sky-600 dark:bg-sky-500/15 dark:text-sky-300">
                <i data-lucide="users" class="h-5 w-5"></i>
            </div>
            <div class="min-w-0">
                <h1 class="text-lg font-bold text-gray-900 dark:text-white">{{ $groupe->nom }}</h1>
                @if ($groupe->description)
                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ $groupe->description }}</p>
                @endif
            </div>
        </div>

        <div class="flex-1 space-y-3 overflow-y-auto bg-gray-50/60 px-3 py-4 sm:px-6" id="messages-container">
            @foreach ($groupe->messages as $message)
                <div class="flex {{ $message->expediteur_type === 'artisan' ? 'justify-end' : 'justify-start' }}">
                    <div class="max-w-[80%] rounded-2xl px-4 py-2.5 sm:max-w-[70%] {{ $message->expediteur_type === 'artisan' ? 'bg-primary text-white shadow-sm' : 'bg-white text-gray-900 shadow-sm ring-1 ring-gray-200 dark:bg-gray-700 dark:text-white dark:ring-gray-600' }}">
                        <div class="mb-1 text-[11px] font-medium {{ $message->expediteur_type === 'artisan' ? 'text-white/80' : 'text-gray-500 dark:text-gray-400' }}">{{ $message->expediteur_nom }}</div>
                        <div class="text-sm whitespace-pre-wrap">{{ $message->contenu }}</div>
                        <div class="mt-1 text-[11px] {{ $message->expediteur_type === 'artisan' ? 'text-white/80' : 'text-gray-500 dark:text-gray-400' }}">{{ $message->created_at->format('d/m/Y H:i') }}</div>
                    </div>
                </div>
            @endforeach
        </div>

        <form method="POST" action="{{ route('artisan.messagerie.groupes.message', $groupe) }}" class="flex gap-2 border-t border-gray-200 bg-white px-3 py-3 dark:border-gray-700 dark:bg-gray-800 sm:px-6">
            @csrf
            <input type="text" name="contenu" required placeholder="Écrivez votre message..." class="flex-1 rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-gray-900 shadow-sm transition focus:border-primary focus:ring-2 focus:ring-primary/10 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
            <button type="submit" class="rounded-xl bg-primary px-6 py-2.5 font-medium text-white transition hover:bg-primary/90">Envoyer</button>
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