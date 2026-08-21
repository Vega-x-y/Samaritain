@extends('layouts.base')

@section('title', 'Conversation - Messagerie - Client')

@section('breadcrumbs')
    <nav class="flex items-center gap-2 text-sm text-muted-foreground">
        <a href="{{ route('client.messagerie.index') }}" class="hover:text-foreground transition-colors flex items-center gap-1">
            <i data-lucide="message-circle" class="w-4 h-4"></i>
            <span>Messagerie</span>
        </a>
        <span class="text-muted-foreground">/</span>
        <span class="text-foreground font-medium truncate max-w-48">{{ $conversation->artisan->business_name ?? 'Artisan' }}</span>
    </nav>
@endsection

@section('content')
<div class="container mx-auto px-3 sm:px-4 py-4 sm:py-8" x-data="conversationApp({{ $conversation->id }})">
    <div class="flex flex-col h-[calc(100vh-160px)] sm:h-[calc(100vh-200px)]">
        <!-- En-tête de la conversation -->
        <div class="flex items-center justify-between mb-4 pb-4 border-b border-gray-200 dark:border-gray-700">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-full bg-orange-100 dark:bg-orange-900/30 flex items-center justify-center text-lg font-bold text-orange-600 dark:text-orange-400">
                    {{ substr($conversation->artisan->business_name ?? 'A', 0, 2) }}
                </div>
                <div>
                    <h1 class="text-lg font-bold text-gray-900 dark:text-white">{{ $conversation->artisan->business_name ?? 'Artisan' }}</h1>
                    @if ($conversation->sujet)
                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ $conversation->sujet }}</p>
                    @endif
                </div>
            </div>
            <form method="POST" action="{{ route('client.messagerie.destroy', $conversation) }}" onsubmit="return confirm('Supprimer cette conversation ?')">
                @csrf @method('DELETE')
                <button type="submit" class="text-red-500 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300 p-2 rounded-lg hover:bg-red-50 dark:hover:bg-red-900/20 transition">
                    <i data-lucide="trash-2" class="w-5 h-5"></i>
                </button>
            </form>
        </div>

        <!-- Messages -->
        <div class="flex-1 overflow-y-auto mb-4 space-y-3" id="messages-container">
            @foreach ($conversation->messages as $message)
                <div class="flex {{ $message->expediteur_type === 'client' ? 'justify-end' : 'justify-start' }}">
                    <div class="max-w-[85%] sm:max-w-[70%] {{ $message->expediteur_type === 'client' ? 'bg-orange-500 text-white' : 'bg-gray-200 dark:bg-gray-700 text-gray-900 dark:text-white' }} rounded-lg px-4 py-2">
                        <div class="text-xs font-medium mb-1 {{ $message->expediteur_type === 'client' ? 'text-orange-100' : 'text-gray-500 dark:text-gray-400' }}">
                            {{ $message->expediteur_nom }}
                        </div>

                        @if ($message->contenu)
                            <div class="text-sm whitespace-pre-wrap">{{ $message->contenu }}</div>
                        @endif

                        @if ($message->document_id && $message->document && $message->document->isDevis())
                            @if (! $message->document->isSigned())
                                <div class="mt-3 pt-2 border-t border-white/20">
                                    <a href="{{ route('client.documents.show', $message->document) }}" class="inline-flex items-center gap-2 bg-orange-500 hover:bg-orange-600 text-white px-4 py-2 rounded-lg text-sm font-medium transition">
                                        <i data-lucide="eye" class="w-4 h-4"></i>
                                        Cliquez ici pour consulter le devis
                                    </a>
                                </div>
                            @else
                                <div class="mt-3 pt-2 border-t border-white/20">
                                    <span class="inline-flex items-center gap-2 bg-green-100 text-green-800 px-4 py-2 rounded-lg text-sm font-medium">
                                        <i data-lucide="check" class="w-4 h-4"></i>
                                        Devis accepté le {{ $message->document->signed_at->format('d/m/Y H:i') }}
                                    </span>
                                </div>
                            @endif
                        @elseif ($message->fichier_path)
                            <div class="mt-2 {{ $message->contenu ? 'pt-2 border-t border-white/20' : '' }}">
                                <a href="{{ Storage::disk('r2')->url($message->fichier_path) }}" target="_blank" class="flex items-center gap-2 text-sm {{ $message->expediteur_type === 'client' ? 'text-orange-100 hover:text-white' : 'text-orange-600 dark:text-orange-400 hover:text-orange-700' }}">
                                    <i data-lucide="file-text" class="w-4 h-4"></i>
                                    <span class="underline">{{ $message->fichier_nom }}</span>
                                    <span class="text-xs opacity-75">({{ number_format($message->fichier_taille / 1024, 1) }} Ko)</span>
                                </a>
                            </div>
                        @endif

                        <div class="text-xs mt-1 flex items-center gap-1 {{ $message->expediteur_type === 'client' ? 'text-orange-100' : 'text-gray-500 dark:text-gray-400' }}">
                            {{ $message->created_at->format('d/m/Y H:i') }}
                            @if ($message->lu)
                                <i data-lucide="check" class="w-3 h-3 text-green-400"></i>
                            @endif
                        </div>
                    </div>
                    <form method="POST" action="{{ route('client.messagerie.message.destroy', $message) }}" onsubmit="return confirm('Supprimer ce message ?')" class="ml-2">
                        @csrf @method('DELETE')
                        <button type="submit" class="text-gray-400 hover:text-red-500 dark:text-gray-500 dark:hover:text-red-400 p-1 rounded transition">
                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                        </button>
                    </form>
                </div>
            @endforeach
        </div>

        <!-- Formulaire d'envoi -->
        <form method="POST" action="{{ route('client.messagerie.message', $conversation) }}" class="flex flex-col gap-2" enctype="multipart/form-data" x-data="{ fileName: '' }">
            @csrf
            <div class="flex min-w-0 gap-2">
                <input type="text" name="contenu" placeholder="Écrivez votre message..."
                    class="flex-1 min-w-0 px-3 sm:px-4 py-2.5 border border-gray-200 dark:border-gray-600 rounded-lg focus:border-orange-500 focus:ring-2 focus:ring-orange-100 dark:focus:ring-orange-900 outline-none transition bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                <input type="file" name="fichier" class="hidden" id="file-input" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" @change="fileName = $event.target.files[0]?.name || ''">
                <button type="button" aria-label="Joindre un fichier" onclick="document.getElementById('file-input').click()" class="shrink-0 bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 px-3 sm:px-4 py-2.5 rounded-lg transition">
                    <i data-lucide="paperclip" class="w-5 h-5"></i>
                </button>
                <button type="submit" class="shrink-0 bg-orange-500 hover:bg-orange-600 text-white px-4 sm:px-6 py-2.5 rounded-lg font-medium transition">
                    Envoyer
                </button>
            </div>
            <div x-show="fileName" x-cloak class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-lg px-3 py-2">
                <i data-lucide="file-text" class="w-4 h-4 text-orange-500"></i>
                <span class="truncate" x-text="fileName"></span>
                <button type="button" @click="fileName = ''; document.getElementById('file-input').value = ''" class="text-gray-400 hover:text-red-500 transition">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('conversationApp', (conversationId) => ({
            pollingInterval: null,

            init() {
                this.pollingInterval = setInterval(() => {
                    this.refreshMessages();
                }, 5000);
            },

            refreshMessages() {
                fetch(`/client/messagerie/${conversationId}?ajax=1`)
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