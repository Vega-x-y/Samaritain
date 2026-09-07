@extends('layouts.base')

@section('title', 'Conversation - Messagerie - Client')

@section('breadcrumbs')
    <nav class="flex items-center gap-2 text-sm text-muted-foreground">
        <a href="{{ route('client.messagerie.index') }}" class="flex items-center gap-1 transition-colors hover:text-foreground">
            <i data-lucide="message-circle" class="h-4 w-4"></i>
            <span>Messagerie</span>
        </a>
        <span class="text-muted-foreground">/</span>
        <span class="max-w-48 truncate font-medium text-foreground">{{ $conversation->artisan->business_name ?? 'Artisan' }}</span>
    </nav>
@endsection

@section('content')
<div class="container mx-auto max-w-5xl px-3 py-4 sm:px-4 sm:py-8" x-data="conversationApp({{ $conversation->id }})">
    <div class="flex h-[calc(100vh-160px)] min-h-[520px] flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white shadow-sm shadow-gray-200/40 ring-1 ring-gray-100 dark:border-gray-700 dark:bg-gray-800 dark:ring-gray-700/70 sm:h-[calc(100vh-200px)]">
        <div class="flex items-center justify-between gap-3 border-b border-gray-200 bg-gradient-to-r from-orange-50 via-white to-white px-4 py-4 dark:border-gray-700 dark:from-orange-500/10 dark:via-gray-800 dark:to-gray-800 sm:px-6">
            <div class="flex min-w-0 items-center gap-3 sm:gap-4">
                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-orange-100 text-sm font-bold text-orange-600 shadow-sm ring-1 ring-orange-100 dark:bg-orange-900/30 dark:text-orange-400 dark:ring-orange-900/40 sm:h-12 sm:w-12 sm:text-lg">
                    {{ strtoupper(substr($conversation->artisan->business_name ?? 'A', 0, 2)) }}
                </div>
                <div class="min-w-0">
                    <h1 class="truncate text-base font-bold text-gray-900 dark:text-white sm:text-lg">{{ $conversation->artisan->business_name ?? 'Artisan' }}</h1>
                    @if ($conversation->sujet)
                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ $conversation->sujet }}</p>
                    @endif
                </div>
            </div>
            <form method="POST" action="{{ route('client.messagerie.destroy', $conversation) }}" onsubmit="return confirm('Supprimer cette conversation ?')">
                @csrf @method('DELETE')
                <button type="submit" aria-label="Supprimer la conversation" class="rounded-xl p-2 text-gray-400 transition hover:bg-red-50 hover:text-red-500 dark:text-gray-500 dark:hover:bg-red-900/20 dark:hover:text-red-400">
                    <i data-lucide="trash-2" class="h-5 w-5"></i>
                </button>
            </form>
        </div>

        <div class="flex-1 space-y-3 overflow-y-auto bg-gradient-to-b from-gray-50/80 to-gray-50 px-3 py-4 sm:px-6" id="messages-container">
            @foreach ($conversation->messages as $message)
                <div class="flex {{ $message->expediteur_type === 'client' ? 'justify-end' : 'justify-start' }} gap-2">
                    @if ($message->type === 'payment_link')
                        @php $meta = is_array($message->metadata) ? $message->metadata : ((array) json_decode((string) $message->metadata, true)); @endphp
                        <div class="max-w-[80%] rounded-2xl border border-orange-200 bg-white p-4 shadow-sm shadow-gray-200/40 dark:border-orange-700 dark:bg-gray-800 sm:max-w-[70%]">
                            <div class="mb-3 flex items-center gap-2">
                                <div class="flex h-8 w-8 items-center justify-center rounded-full bg-orange-100 dark:bg-orange-900/30">
                                    <i data-lucide="link" class="h-4 w-4 text-orange-600 dark:text-orange-400"></i>
                                </div>
                                <span class="text-sm font-semibold text-gray-800 dark:text-white">Demande de paiement</span>
                            </div>
                            <div class="mb-3 space-y-1 text-xs text-gray-500 dark:text-gray-400">
                                <div class="flex justify-between gap-3">
                                    <span>Montant total</span>
                                    <span class="font-medium text-gray-800 dark:text-white">{{ number_format($meta['total_amount'] ?? 0, 0, ',', ' ') }} FCFA</span>
                                </div>
                                <div class="flex justify-between gap-3">
                                    <span>Acompte demandé</span>
                                    <span class="font-medium text-orange-600 dark:text-orange-400">{{ number_format($meta['down_payment_amount'] ?? 0, 0, ',', ' ') }} FCFA</span>
                                </div>
                            </div>
                            @if ($message->expediteur_type !== 'client')
                                <a href="{{ $meta['deposit_url'] ?? '#' }}" class="block w-full rounded-xl bg-orange-500 px-4 py-2.5 text-center text-sm font-medium text-white transition hover:bg-orange-600">Payer l'acompte</a>
                            @else
                                <span class="block w-full rounded-xl bg-orange-50 px-4 py-2.5 text-center text-xs text-gray-500 dark:bg-orange-500/10 dark:text-gray-300">Lien envoyé au client</span>
                            @endif
                            <div class="mt-2 text-right text-[11px] text-gray-400 dark:text-gray-500">{{ $message->created_at->format('d/m/Y H:i') }}</div>
                        </div>
                    @else
                        <div class="max-w-[85%] rounded-2xl px-4 py-2.5 sm:max-w-[70%] {{ $message->expediteur_type === 'client' ? 'bg-orange-500 text-white shadow-sm shadow-orange-200/60' : 'bg-white text-gray-900 shadow-sm shadow-gray-200/50 ring-1 ring-gray-200 dark:bg-gray-700 dark:text-white dark:ring-gray-600' }}">
                            <div class="mb-1 text-[11px] font-medium {{ $message->expediteur_type === 'client' ? 'text-orange-100' : 'text-gray-500 dark:text-gray-400' }}">{{ $message->expediteur_nom }}</div>

                            @if ($message->contenu)
                                <div class="text-sm whitespace-pre-wrap">{{ $message->contenu }}</div>
                            @endif

                            @if ($message->document_id && $message->document && $message->document->isDevis())
                                @if (! $message->document->isSigned())
                                    <div class="mt-3 border-t border-white/20 pt-2">
                                        <a href="{{ route('client.documents.show', $message->document) }}" class="inline-flex items-center gap-2 rounded-lg bg-orange-500 px-4 py-2 text-sm font-medium text-white transition hover:bg-orange-600">
                                            <i data-lucide="eye" class="h-4 w-4"></i>
                                            Cliquez ici pour consulter le devis
                                        </a>
                                    </div>
                                @else
                                    <div class="mt-3 border-t border-white/20 pt-2">
                                        <span class="inline-flex items-center gap-2 rounded-lg bg-green-100 px-4 py-2 text-sm font-medium text-green-800">
                                            <i data-lucide="check" class="h-4 w-4"></i>
                                            Devis accepté le {{ $message->document->signed_at->format('d/m/Y H:i') }}
                                        </span>
                                    </div>
                                @endif
                            @elseif ($message->fichier_path)
                            @php
                                $fileUrl = rtrim((string) config('filesystems.disks.r2.url', ''), '/') . '/' . ltrim((string) $message->fichier_path, '/');
                            @endphp
                            <div class="mt-2 {{ $message->contenu ? 'border-t border-white/20 pt-2' : '' }}">
                                <a href="{{ $fileUrl }}" target="_blank" class="flex items-center gap-2 text-sm {{ $message->expediteur_type === 'client' ? 'text-orange-100 hover:text-white' : 'text-orange-600 dark:text-orange-400 hover:text-orange-700' }}">
                                        <span class="underline">{{ $message->fichier_nom }}</span>
                                        <span class="text-xs opacity-75">({{ number_format($message->fichier_taille / 1024, 1) }} Ko)</span>
                                    </a>
                                </div>
                            @endif

                            <div class="mt-1 flex items-center gap-1 text-[11px] {{ $message->expediteur_type === 'client' ? 'text-orange-100' : 'text-gray-500 dark:text-gray-400' }}">
                                {{ $message->created_at->format('d/m/Y H:i') }}
                                @if ($message->lu)
                                    <i data-lucide="check" class="h-3 w-3 text-green-400"></i>
                                @endif
                            </div>
                        </div>
                    @endif
                    <form method="POST" action="{{ route('client.messagerie.message.destroy', $message) }}" onsubmit="return confirm('Supprimer ce message ?')" class="self-end">
                        @csrf @method('DELETE')
                        <button type="submit" class="rounded-lg p-1 text-gray-400 transition hover:bg-red-50 hover:text-red-500 dark:text-gray-500 dark:hover:bg-red-900/20 dark:hover:text-red-400">
                            <i data-lucide="trash-2" class="h-4 w-4"></i>
                        </button>
                    </form>
                </div>
            @endforeach
        </div>

        <form method="POST" action="{{ route('client.messagerie.message', $conversation) }}" class="flex flex-col gap-2 border-t border-gray-200 bg-white px-3 py-3 dark:border-gray-700 dark:bg-gray-800 sm:px-6" enctype="multipart/form-data" x-data="{ fileName: '' }">
            @csrf
            <div class="flex min-w-0 gap-2">
                <input type="text" name="contenu" placeholder="Écrivez votre message..." class="flex-1 min-w-0 rounded-xl border border-gray-200 bg-white px-3 py-2.5 text-gray-900 shadow-sm transition focus:border-orange-500 focus:ring-2 focus:ring-orange-100 dark:border-gray-600 dark:bg-gray-700 dark:text-white sm:px-4">
                <input type="file" name="fichier" class="hidden" id="file-input" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" @change="fileName = $event.target.files[0]?.name || ''">
                <button type="button" aria-label="Joindre un fichier" onclick="document.getElementById('file-input').click()" class="shrink-0 rounded-xl bg-gray-200 px-3 py-2.5 text-gray-700 transition hover:bg-gray-300 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600 sm:px-4">
                    <i data-lucide="paperclip" class="h-5 w-5"></i>
                </button>
                <button type="submit" class="shrink-0 rounded-xl bg-orange-500 px-4 py-2.5 font-medium text-white transition hover:bg-orange-600 sm:px-6">Envoyer</button>
            </div>
            <div x-show="fileName" x-cloak class="flex items-center gap-2 rounded-xl bg-gray-100 px-3 py-2 text-sm text-gray-600 dark:bg-gray-700 dark:text-gray-300">
                <i data-lucide="file-text" class="h-4 w-4 text-orange-500"></i>
                <span class="truncate" x-text="fileName"></span>
                <button type="button" @click="fileName = ''; document.getElementById('file-input').value = ''" class="ml-auto text-gray-400 transition hover:text-red-500">
                    <i data-lucide="x" class="h-4 w-4"></i>
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