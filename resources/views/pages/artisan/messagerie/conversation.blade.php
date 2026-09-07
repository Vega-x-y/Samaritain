@extends('layouts.artisan')

@section('title', 'Conversation - Messagerie - Artisan')

@section('breadcrumbs')
    <nav class="flex items-center gap-2 text-sm text-muted-foreground">
        <a href="{{ route('artisan.messagerie.index') }}" class="flex items-center gap-1 transition-colors hover:text-foreground">
            <i data-lucide="message-circle" class="h-4 w-4"></i>
            <span>Messagerie</span>
        </a>
        <span class="text-muted-foreground">/</span>
        <span class="max-w-48 truncate font-medium text-foreground">{{ $conversation->participant_name }}</span>
    </nav>
@endsection

@section('content')
<div class="container mx-auto max-w-5xl px-3 py-4 sm:px-4 sm:py-8" x-data="conversationApp({{ $conversation->id }})">
    <div class="flex h-[calc(100vh-160px)] min-h-[520px] flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white shadow-sm shadow-gray-200/40 ring-1 ring-gray-100 dark:border-gray-700 dark:bg-gray-800 dark:ring-gray-700/70 sm:h-[calc(100vh-200px)]">
        <div class="flex items-center justify-between gap-3 border-b border-gray-200 bg-gradient-to-r from-primary/5 via-white to-white px-4 py-4 dark:border-gray-700 dark:from-primary/10 dark:via-gray-800 dark:to-gray-800 sm:px-6">
            <div class="flex min-w-0 items-center gap-3 sm:gap-4">
                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-primary/10 text-sm font-bold text-primary shadow-sm ring-1 ring-primary/10 dark:bg-primary/20 dark:ring-primary/20 sm:h-12 sm:w-12 sm:text-lg">
                    {{ strtoupper(substr($conversation->participant_name, 0, 2)) }}
                </div>
                <div class="min-w-0">
                    <h1 class="truncate text-base font-bold text-gray-900 dark:text-white sm:text-lg">{{ $conversation->participant_name }}</h1>
                    @if ($conversation->sujet)
                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ $conversation->sujet }}</p>
                    @endif
                </div>
            </div>
            <form method="POST" action="{{ route('artisan.messagerie.conversation.destroy', $conversation) }}" onsubmit="return confirm('Supprimer cette conversation ?')">
                @csrf @method('DELETE')
                <button type="submit" aria-label="Supprimer la conversation" class="rounded-xl p-2 text-gray-400 transition hover:bg-red-50 hover:text-red-500 dark:text-gray-500 dark:hover:bg-red-900/20 dark:hover:text-red-400">
                    <i data-lucide="trash-2" class="h-5 w-5"></i>
                </button>
            </form>
        </div>

        <div class="flex-1 space-y-3 overflow-y-auto bg-gradient-to-b from-gray-50/80 to-gray-50 px-3 py-4 sm:px-6" id="messages-container">
            @foreach ($conversation->messages as $message)
                <div class="flex {{ $message->expediteur_type === 'artisan' ? 'justify-end' : 'justify-start' }} gap-2">
                    @if ($message->type === 'payment_link')
                        @php $meta = is_array($message->metadata) ? $message->metadata : ((array) json_decode((string) $message->metadata, true)); @endphp
                        <div class="max-w-[80%] rounded-2xl border border-primary/20 bg-white p-4 shadow-sm shadow-gray-200/40 dark:border-primary/30 dark:bg-gray-800 sm:max-w-[70%]">
                            <div class="mb-3 flex items-center gap-2">
                                <div class="flex h-8 w-8 items-center justify-center rounded-full bg-primary/10 dark:bg-primary/20">
                                    <i data-lucide="link" class="h-4 w-4 text-primary"></i>
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
                                    <span class="font-medium text-primary dark:text-primary">{{ number_format($meta['down_payment_amount'] ?? 0, 0, ',', ' ') }} FCFA</span>
                                </div>
                            </div>
                            @if ($message->expediteur_type !== 'artisan')
                                <a href="{{ $meta['deposit_url'] ?? '#' }}" class="block w-full rounded-xl bg-primary px-4 py-2.5 text-center text-sm font-medium text-white transition hover:bg-primary/90">Payer l'acompte</a>
                            @else
                                <span class="block w-full rounded-xl bg-primary/5 px-4 py-2.5 text-center text-xs text-gray-500 dark:bg-primary/10 dark:text-gray-300">Lien envoyé au client</span>
                            @endif
                            <div class="mt-2 text-right text-[11px] text-gray-400 dark:text-gray-500">{{ $message->created_at->format('d/m/Y H:i') }}</div>
                        </div>
                    @else
                        <div class="max-w-[85%] rounded-2xl px-4 py-2.5 sm:max-w-[70%] {{ $message->expediteur_type === 'artisan' ? 'bg-primary text-white shadow-sm shadow-primary/20' : 'bg-white text-gray-900 shadow-sm shadow-gray-200/50 ring-1 ring-gray-200 dark:bg-gray-700 dark:text-white dark:ring-gray-600' }}">
                            <div class="mb-1 text-[11px] font-medium {{ $message->expediteur_type === 'artisan' ? 'text-white/80' : 'text-gray-500 dark:text-gray-400' }}">{{ $message->expediteur_nom }}</div>

                            @if ($message->contenu)
                                <div class="text-sm whitespace-pre-wrap">{{ $message->contenu }}</div>
                            @endif

                            @if ($message->fichier_path)
                                @php
                                    $fileUrl = rtrim((string) config('filesystems.disks.r2.url', ''), '/') . '/' . ltrim((string) $message->fichier_path, '/');
                                @endphp
                                <div class="mt-2 {{ $message->contenu ? 'border-t border-white/20 pt-2' : '' }}">
                                    <a href="{{ $fileUrl }}" target="_blank" class="flex items-center gap-2 text-sm {{ $message->expediteur_type === 'artisan' ? 'text-white/80 hover:text-white' : 'text-primary dark:text-primary hover:text-primary' }}">
                                        <i data-lucide="file-text" class="h-4 w-4"></i>
                                        <span class="underline">{{ $message->fichier_nom }}</span>
                                        <span class="text-xs opacity-75">({{ number_format($message->fichier_taille / 1024, 1) }} Ko)</span>
                                    </a>
                                </div>
                            @endif

                            <div class="mt-1 flex items-center gap-1 text-[11px] {{ $message->expediteur_type === 'artisan' ? 'text-white/80' : 'text-gray-500 dark:text-gray-400' }}">
                                {{ $message->created_at->format('d/m/Y H:i') }}
                                @if ($message->lu)
                                    <i data-lucide="check" class="h-3 w-3 text-green-400"></i>
                                @endif
                            </div>
                        </div>
                    @endif
                    <form method="POST" action="{{ route('artisan.messagerie.message.destroy', $message) }}" onsubmit="return confirm('Supprimer ce message ?')" class="self-end">
                        @csrf @method('DELETE')
                        <button type="submit" class="rounded-lg p-1 text-gray-400 transition hover:bg-red-50 hover:text-red-500 dark:text-gray-500 dark:hover:bg-red-900/20 dark:hover:text-red-400">
                            <i data-lucide="trash-2" class="h-4 w-4"></i>
                        </button>
                    </form>
                </div>
            @endforeach
        </div>

        <div x-data="{ showPaymentModal: false, totalAmount: '', downPayment: '' }">
            <div x-show="showPaymentModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/50" @keydown.escape.window="showPaymentModal = false">
                <div class="mx-4 w-full max-w-md rounded-2xl bg-white p-6 shadow-xl dark:bg-gray-800">
                    <div class="mb-4 flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Demander un acompte</h3>
                        <button type="button" @click="showPaymentModal = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                            <i data-lucide="x" class="h-5 w-5"></i>
                        </button>
                    </div>
                    <form method="POST" action="{{ route('artisan.payment-link.store', $conversation) }}">
                        @csrf
                        <div class="space-y-4">
                            <div>
                                <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Montant total (FCFA)</label>
                                <input type="number" name="total_amount" x-model="totalAmount" min="1" required class="w-full rounded-lg border-gray-300 bg-white px-4 py-2.5 text-gray-900 shadow-sm transition focus:border-primary focus:ring-2 focus:ring-primary/10 dark:border-gray-600 dark:bg-gray-700 dark:text-white" placeholder="Ex: 50000">
                            </div>
                            <div>
                                <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Acompte demandé (FCFA)</label>
                                <input type="number" name="down_payment_amount" x-model="downPayment" min="1" :max="totalAmount" required class="w-full rounded-lg border-gray-300 bg-white px-4 py-2.5 text-gray-900 shadow-sm transition focus:border-primary focus:ring-2 focus:ring-primary/10 dark:border-gray-600 dark:bg-gray-700 dark:text-white" placeholder="Ex: 15000">
                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Une commission de <strong>{{ \App\Models\Setting::getValue('artisan_commission_percent', 5) }}%</strong> sera déduite.</p>
                            </div>
                        </div>
                        <div class="mt-6 flex gap-3">
                            <button type="button" @click="showPaymentModal = false" class="flex-1 rounded-xl border border-gray-300 px-4 py-2.5 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700">Annuler</button>
                            <button type="submit" class="flex-1 rounded-xl bg-primary px-4 py-2.5 text-sm font-medium text-white transition hover:bg-primary/90">Envoyer le lien</button>
                        </div>
                    </form>
                </div>
            </div>

            <form method="POST" action="{{ route('artisan.messagerie.message', $conversation) }}" class="flex flex-col gap-2 border-t border-gray-200 bg-white px-3 py-3 dark:border-gray-700 dark:bg-gray-800 sm:px-6" enctype="multipart/form-data" x-data="{ fileName: '' }">
                @csrf
                <div class="flex gap-2">
                        <div class="min-w-0 flex-1">
                            <x-form.textarea
                                name="contenu"
                                rows="1"
                                placeholder="Écrivez votre message..."
                                class="min-h-11 rounded-xl border-gray-200 px-4 py-2.5 focus:border-primary focus:ring-primary/10 dark:border-gray-600 dark:bg-gray-700"
                            />
                        </div>
                    <input type="file" name="fichier" class="hidden" id="file-input" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" @change="fileName = $event.target.files[0]?.name || ''">
                    <button type="button" onclick="document.getElementById('file-input').click()" class="rounded-xl bg-gray-200 px-4 py-2.5 text-gray-700 transition hover:bg-gray-300 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600" title="Joindre un fichier">
                        <i data-lucide="paperclip" class="h-5 w-5"></i>
                    </button>
                    <button type="button" @click="showPaymentModal = true" class="rounded-xl bg-primary/10 px-4 py-2.5 text-primary transition hover:bg-primary/20 dark:bg-primary/20 dark:text-primary" title="Demander un acompte">
                        <i data-lucide="link" class="h-5 w-5"></i>
                    </button>
                    <button type="submit" aria-label="Envoyer le message" title="Envoyer le message" class="rounded-xl bg-primary px-4 py-2.5 font-medium text-white transition hover:bg-primary/90 sm:px-5">
                        <i data-lucide="send" class="h-5 w-5"></i>
                    </button>
                </div>
                <div x-show="fileName" x-cloak class="flex items-center gap-2 rounded-xl bg-gray-100 px-3 py-2 text-sm text-gray-600 dark:bg-gray-700 dark:text-gray-300">
                    <i data-lucide="file-text" class="h-4 w-4 text-primary"></i>
                    <span class="truncate" x-text="fileName"></span>
                    <button type="button" @click="fileName = ''; document.getElementById('file-input').value = ''" class="ml-auto text-gray-400 transition hover:text-red-500">
                        <i data-lucide="x" class="h-4 w-4"></i>
                    </button>
                </div>
            </form>
        </div>
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
                fetch(`/artisan/messagerie/conversation/${conversationId}?ajax=1`)
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