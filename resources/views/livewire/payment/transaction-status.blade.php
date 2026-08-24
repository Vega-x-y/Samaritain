<div wire:poll.{{ $shouldPoll ? $pollingInterval : 'keep-alive' }}ms>
    @if($transaction)
        <flux:card>
            {{-- Header with status badge --}}
            <div class="flex justify-between items-start mb-6">
                <div>
                    <flux:heading size="lg">Détails de la transaction</flux:heading>
                    <flux:text class="text-sm text-gray-500 dark:text-gray-400 font-mono mt-1">
                        {{ $transaction->transaction_id }}
                    </flux:text>
                </div>
                <flux:badge variant="{{ $transaction->status->variant() }}" size="lg">
                    {{ $transaction->status->label() }}
                </flux:badge>
            </div>

            {{-- Success/Error messages --}}
            @if($successMessage)
                <flux:callout variant="success" class="mb-4">
                    {{ $successMessage }}
                </flux:callout>
            @endif

            @if($errorMessage)
                <flux:callout variant="danger" class="mb-4">
                    {{ $errorMessage }}
                </flux:callout>
            @endif

            {{-- Transaction details grid --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                {{-- Type --}}
                <div>
                    <flux:text class="text-sm text-gray-600 dark:text-gray-400">Type</flux:text>
                    <flux:heading size="sm" class="mt-1">{{ $transaction->type->label() }}</flux:heading>
                </div>

                {{-- Amount --}}
                <div>
                    <flux:text class="text-sm text-gray-600 dark:text-gray-400">Montant</flux:text>
                    <flux:heading size="sm" class="mt-1">
                        {{ number_format($transaction->amount / 100, 0, ',', ' ') }} {{ $transaction->currency }}
                    </flux:heading>
                </div>

                {{-- Provider --}}
                <div>
                    <flux:text class="text-sm text-gray-600 dark:text-gray-400">Opérateur</flux:text>
                    <flux:heading size="sm" class="mt-1">
                        @if($transaction->provider)
                            {{ config('pawapay.providers.' . $transaction->provider, $transaction->provider) }}
                        @else
                            <span class="text-gray-400">Non spécifié</span>
                        @endif
                    </flux:heading>
                </div>

                {{-- Created at --}}
                <div>
                    <flux:text class="text-sm text-gray-600 dark:text-gray-400">Date de création</flux:text>
                    <flux:heading size="sm" class="mt-1">
                        {{ $transaction->created_at->format('d/m/Y à H:i:s') }}
                    </flux:heading>
                </div>

                {{-- Updated at --}}
                <div>
                    <flux:text class="text-sm text-gray-600 dark:text-gray-400">Dernière mise à jour</flux:text>
                    <flux:heading size="sm" class="mt-1">
                        {{ $transaction->updated_at->format('d/m/Y à H:i:s') }}
                    </flux:heading>
                </div>

                {{-- PawaPay ID --}}
                <div>
                    <flux:text class="text-sm text-gray-600 dark:text-gray-400">ID PawaPay</flux:text>
                    <flux:text class="text-sm font-mono mt-1">
                        {{ $transaction->pawapay_id }}
                    </flux:text>
                </div>
            </div>

            {{-- Failure reason if failed --}}
            @if($transaction->is_failed && $transaction->failure_reason)
                <div class="mb-6">
                    <flux:callout variant="danger">
                        <div>
                            <flux:text class="font-semibold">Raison de l'échec :</flux:text>
                            <flux:text class="mt-1">{{ $transaction->failure_reason }}</flux:text>
                        </div>
                    </flux:callout>
                </div>
            @endif

            {{-- Action buttons --}}
            <div class="flex flex-wrap gap-3 mb-6">
                {{-- Refresh status button --}}
                <flux:button 
                    wire:click="refreshStatus" 
                    variant="primary"
                    :disabled="$processing"
                    icon="arrow-path"
                >
                    @if($processing)
                        <span wire:loading wire:target="refreshStatus">
                            Vérification...
                        </span>
                    @else
                        Vérifier le statut
                    @endif
                </flux:button>

                {{-- Retry button (only for failed transactions) --}}
                @if($transaction->is_failed)
                    <flux:button 
                        wire:click="retry" 
                        variant="outline"
                        :disabled="$processing"
                        icon="arrow-path"
                    >
                        @if($processing)
                            <span wire:loading wire:target="retry">
                                Création...
                            </span>
                        @else
                            Réessayer
                        @endif
                    </flux:button>
                @endif

                {{-- Back to list --}}
                <a href="{{ route('tenant.payments') }}" wire:navigate>
                    <flux:button variant="ghost">
                        Retour à la liste
                    </flux:button>
                </a>
            </div>

            {{-- Auto-polling indicator --}}
            @if($shouldPoll)
                <div class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400 mb-6">
                    <flux:icon icon="arrow-path" class="w-4 h-4 animate-spin" />
                    <flux:text>Actualisation automatique toutes les 5 secondes...</flux:text>
                </div>
            @endif

            {{-- Raw response (collapsible) --}}
            <div class="border-t pt-6">
                <details class="group">
                    <summary class="cursor-pointer list-none">
                        <div class="flex items-center gap-2">
                            <flux:icon icon="chevron-right" class="w-4 h-4 transition-transform group-open:rotate-90" />
                            <flux:heading size="sm">Réponse brute de l'API</flux:heading>
                        </div>
                    </summary>
                    <div class="mt-4 bg-gray-100 dark:bg-gray-800 p-4 rounded-lg overflow-x-auto">
                        <pre class="text-xs text-gray-800 dark:text-gray-200">{{ $this->formattedRawResponse }}</pre>
                    </div>
                </details>
            </div>
        </flux:card>
    @else
        <flux:card>
            <flux:callout variant="danger">
                Transaction non trouvée.
            </flux:callout>
        </flux:card>
    @endif
</div>
