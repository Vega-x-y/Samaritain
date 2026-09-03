@extends('layouts.artisan')

@section('title', 'Conversation - Messagerie - Artisan')

@section('breadcrumbs')
    <nav class="flex items-center gap-2 text-sm text-muted-foreground">
        <a href="{{ route('artisan.messagerie.index') }}" class="hover:text-foreground transition-colors flex items-center gap-1">
            <i data-lucide="message-circle" class="w-4 h-4"></i>
            <span>Messagerie</span>
        </a>
        <span class="text-muted-foreground">/</span>
        <span class="text-foreground font-medium truncate max-w-48">{{ $conversation->participant_name }}</span>
    </nav>
@endsection

@section('content')
<div class="container mx-auto px-4 py-8" x-data="conversationApp({{ $conversation->id }})">
    <div class="flex flex-col h-[calc(100vh-200px)]">
        <!-- En-tête de la conversation -->
        <div class="flex items-center justify-between mb-4 pb-4 border-b border-gray-200 dark:border-gray-700">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-full bg-primary/10 dark:bg-primary/20 flex items-center justify-center text-lg font-bold text-primary dark:text-primary">
                    {{ substr($conversation->participant_name, 0, 2) }}
                </div>
                <div>
                    <h1 class="text-lg font-bold text-gray-900 dark:text-white">{{ $conversation->participant_name }}</h1>
                    @if ($conversation->sujet)
                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ $conversation->sujet }}</p>
                    @endif
                </div>
            </div>
            <form method="POST" action="{{ route('artisan.messagerie.conversation.destroy', $conversation) }}" onsubmit="return confirm('Supprimer cette conversation ?')">
                @csrf @method('DELETE')
                <button type="submit" class="text-red-500 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300 p-2 rounded-lg hover:bg-red-50 dark:hover:bg-red-900/20 transition">
                    <i data-lucide="trash-2" class="w-5 h-5"></i>
                </button>
            </form>
        </div>

        <!-- Messages -->
        <div class="flex-1 overflow-y-auto mb-4 space-y-3" id="messages-container">
            @foreach ($conversation->messages as $message)
                <div class="flex {{ $message->expediteur_type === 'artisan' ? 'justify-end' : 'justify-start' }}">
                    @if ($message->type === 'payment_link')
                        {{-- Carte de paiement --}}
                        {{-- metadata may be double-encoded (legacy rows), decode defensively --}}
@php $meta = is_array($message->metadata) ? $message->metadata : ((array) json_decode((string) $message->metadata, true)); @endphp
                        <div class="max-w-[70%] bg-white dark:bg-gray-800 border border-primary/20 dark:border-primary rounded-xl shadow-sm p-4">
                            <div class="flex items-center gap-2 mb-3">
                                <div class="w-8 h-8 bg-primary/10 dark:bg-primary/20 rounded-full flex items-center justify-center">
                                    <i data-lucide="link" class="w-4 h-4 text-primary dark:text-primary"></i>
                                </div>
                                <span class="text-sm font-semibold text-gray-800 dark:text-white">Demande de paiement</span>
                            </div>
                            <div class="text-xs text-gray-500 dark:text-gray-400 space-y-1 mb-3">
                                <div class="flex justify-between">
                                    <span>Montant total</span>
                                    <span class="font-medium text-gray-800 dark:text-white">{{ number_format($meta['total_amount'] ?? 0, 0, ',', ' ') }} FCFA</span>
                                </div>
                                <div class="flex justify-between">
                                    <span>Acompte demandé</span>
                                    <span class="font-medium text-primary dark:text-primary">{{ number_format($meta['down_payment_amount'] ?? 0, 0, ',', ' ') }} FCFA</span>
                                </div>
                            </div>
                            @if ($message->expediteur_type !== 'artisan')
                                <a href="{{ $meta['deposit_url'] ?? '#' }}"
                                   class="block w-full text-center bg-primary hover:bg-primary/90 text-white text-sm font-medium py-2 px-4 rounded-lg transition">
                                    Payer l'acompte
                                </a>
                            @else
                                <span class="block w-full text-center text-xs text-gray-400 dark:text-gray-500 py-2">Lien envoyé au client</span>
                            @endif
                            <div class="text-xs mt-2 text-gray-400 dark:text-gray-500 text-right">
                                {{ $message->created_at->format('d/m/Y H:i') }}
                            </div>
                        </div>
                    @else
                        <div class="max-w-[70%] {{ $message->expediteur_type === 'artisan' ? 'bg-primary text-white' : 'bg-gray-200 dark:bg-gray-700 text-gray-900 dark:text-white' }} rounded-lg px-4 py-2">
                            <div class="text-xs font-medium mb-1 {{ $message->expediteur_type === 'artisan' ? 'text-white/80' : 'text-gray-500 dark:text-gray-400' }}">
                                {{ $message->expediteur_nom }}
                            </div>

                            @if ($message->contenu)
                                <div class="text-sm whitespace-pre-wrap">{{ $message->contenu }}</div>
                            @endif

                            @if ($message->fichier_path)
                                <div class="mt-2 {{ $message->contenu ? 'pt-2 border-t border-white/20' : '' }}">
                                    <a href="{{ Storage::disk('r2')->url($message->fichier_path) }}" target="_blank" class="flex items-center gap-2 text-sm {{ $message->expediteur_type === 'artisan' ? 'text-white/80 hover:text-white' : 'text-primary dark:text-primary hover:text-primary' }}">
                                        <i data-lucide="file-text" class="w-4 h-4"></i>
                                        <span class="underline">{{ $message->fichier_nom }}</span>
                                        <span class="text-xs opacity-75">({{ number_format($message->fichier_taille / 1024, 1) }} Ko)</span>
                                    </a>
                                </div>
                            @endif

                            <div class="text-xs mt-1 flex items-center gap-1 {{ $message->expediteur_type === 'artisan' ? 'text-white/80' : 'text-gray-500 dark:text-gray-400' }}">
                                {{ $message->created_at->format('d/m/Y H:i') }}
                                @if ($message->lu)
                                    <i data-lucide="check" class="w-3 h-3 text-green-400"></i>
                                @endif
                            </div>
                        </div>
                    @endif
                    <form method="POST" action="{{ route('artisan.messagerie.message.destroy', $message) }}" onsubmit="return confirm('Supprimer ce message ?')" class="ml-2">
                        @csrf @method('DELETE')
                        <button type="submit" class="text-gray-400 hover:text-red-500 dark:text-gray-500 dark:hover:text-red-400 p-1 rounded transition">
                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                        </button>
                    </form>
                </div>
            @endforeach
        </div>

        <!-- Formulaire d'envoi -->
        <div x-data="{ showPaymentModal: false, totalAmount: '', downPayment: '' }">
            {{-- Modale de demande de paiement --}}
            <div x-show="showPaymentModal" x-cloak
                 class="fixed inset-0 z-50 flex items-center justify-center bg-black/50"
                 @keydown.escape.window="showPaymentModal = false">
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-xl p-6 w-full max-w-md mx-4">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Demander un acompte</h3>
                        <button type="button" @click="showPaymentModal = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                            <i data-lucide="x" class="w-5 h-5"></i>
                        </button>
                    </div>
                    <form method="POST" action="{{ route('artisan.payment-link.store', $conversation) }}">
                        @csrf
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Montant total (FCFA)</label>
                                <input type="number" name="total_amount" x-model="totalAmount" min="1" required
                                       class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-primary focus:ring-primary"
                                       placeholder="Ex: 50000">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Acompte demandé (FCFA)</label>
                                <input type="number" name="down_payment_amount" x-model="downPayment" min="1" :max="totalAmount" required
                                       class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-primary focus:ring-primary"
                                       placeholder="Ex: 15000">
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                    Une commission de <strong>{{ \App\Models\Setting::getValue('artisan_commission_percent', 5) }}%</strong> sera déduite.
                                </p>
                            </div>
                        </div>
                        <div class="flex gap-3 mt-6">
                            <button type="button" @click="showPaymentModal = false"
                                    class="flex-1 py-2 px-4 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition text-sm font-medium">
                                Annuler
                            </button>
                            <button type="submit"
                                    class="flex-1 py-2 px-4 bg-primary hover:bg-primary/90 text-white rounded-lg transition text-sm font-medium">
                                Envoyer le lien
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Barre d'envoi --}}
            <form method="POST" action="{{ route('artisan.messagerie.message', $conversation) }}" class="flex flex-col gap-2" enctype="multipart/form-data" x-data="{ fileName: '' }">
                @csrf
                <div class="flex gap-2">
                    <input type="text" name="contenu" placeholder="�?crivez votre message..."
                        class="flex-1 px-4 py-2.5 border border-gray-200 dark:border-gray-600 rounded-lg focus:border-primary focus:ring-2 focus:ring-primary/10 dark:focus:ring-primary/20 outline-none transition bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                    <input type="file" name="fichier" class="hidden" id="file-input" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" @change="fileName = $event.target.files[0]?.name || ''">
                    <button type="button" onclick="document.getElementById('file-input').click()" class="bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 px-4 py-2.5 rounded-lg transition" title="Joindre un fichier">
                        <i data-lucide="paperclip" class="w-5 h-5"></i>
                    </button>
                    <button type="button" @click="showPaymentModal = true"
                            class="bg-primary/10 dark:bg-primary/20 hover:bg-primary/20 dark:hover:bg-primary/30 text-primary dark:text-primary px-4 py-2.5 rounded-lg transition" title="Demander un acompte">
                        <i data-lucide="link" class="w-5 h-5"></i>
                    </button>
                    <button type="submit" class="bg-primary hover:bg-primary/90 text-white px-6 py-2.5 rounded-lg font-medium transition">
                        Envoyer
                    </button>
                </div>
                <div x-show="fileName" x-cloak class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-lg px-3 py-2">
                    <i data-lucide="file-text" class="w-4 h-4 text-primary"></i>
                    <span class="truncate" x-text="fileName"></span>
                    <button type="button" @click="fileName = ''; document.getElementById('file-input').value = ''" class="text-gray-400 hover:text-red-500 transition">
                        <i data-lucide="x" class="w-4 h-4"></i>
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