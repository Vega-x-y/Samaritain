<div>
    <flux:card>
        <flux:heading size="lg" class="mb-4">Effectuer un paiement</flux:heading>

        {{-- Success state --}}
        @if($transaction && $transaction->status === \App\Enums\TransactionStatus::COMPLETED)
            <div class="mb-4">
                <flux:badge variant="success" size="lg">
                    Paiement réussi !
                </flux:badge>
                <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                    Votre paiement de {{ number_format($amount / 100, 0, ',', ' ') }} {{ config('pawapay.default_currency') }} a été effectué avec succès.
                </p>
            </div>
        @elseif($transaction && $transaction->status === \App\Enums\TransactionStatus::ACCEPTED)
            <div class="mb-4">
                <flux:badge variant="warning" size="lg">
                    En attente de confirmation
                </flux:badge>
                <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                    Veuillez confirmer le paiement sur votre téléphone en entrant votre code PIN.
                </p>
            </div>
        @else
            <div class="space-y-4">
                {{-- Amount display --}}
                <div class="bg-gray-100 dark:bg-gray-800 p-4 rounded-lg">
                    <flux:text class="text-sm text-gray-600 dark:text-gray-400">Montant à payer</flux:text>
                    <flux:heading size="xl" class="mt-1">
                        {{ number_format($amount / 100, 0, ',', ' ') }} {{ config('pawapay.default_currency') }}
                    </flux:heading>
                </div>

                <div class="border-t pt-4">
                    <flux:text class="text-sm text-gray-600 dark:text-gray-400 mb-3">
                        Le paiement sera directement initié auprès de votre opérateur Mobile Money.
                    </flux:text>

                    <flux:input wire:model="phoneNumber" label="Numéro Mobile Money" type="tel" required />
                    <flux:select wire:model="provider" label="Opérateur" required>
                        <flux:select.option value="">Choisir l'opérateur</flux:select.option>
                        @foreach($this->providers as $code => $label)
                            <flux:select.option value="{{ $code }}">{{ $label }}</flux:select.option>
                        @endforeach
                    </flux:select>

                    <flux:button
                        wire:click="initiateDeposit"
                        variant="primary"
                        class="w-full"
                        :disabled="$processing"
                    >
                        @if($processing)
                            <span wire:loading wire:target="initiateDeposit">
                                Confirmation en cours...
                            </span>
                        @else
                            Confirmer le paiement
                        @endif
                    </flux:button>
                </div>

                {{-- Error message --}}
                @if($errorMessage)
                    <flux:callout variant="danger" class="mt-4">
                        {{ $errorMessage }}
                    </flux:callout>
                @endif
            </div>
        @endif
    </flux:card>

</div>
