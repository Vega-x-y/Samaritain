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
            {{-- Payment form --}}
            <div class="space-y-4">
                {{-- Amount display --}}
                <div class="bg-gray-100 dark:bg-gray-800 p-4 rounded-lg">
                    <flux:text class="text-sm text-gray-600 dark:text-gray-400">Montant à payer</flux:text>
                    <flux:heading size="xl" class="mt-1">
                        {{ number_format($amount / 100, 0, ',', ' ') }} {{ config('pawapay.default_currency') }}
                    </flux:heading>
                </div>

                {{-- Direct payment form --}}
                <div class="border-t pt-4">
                    <flux:heading size="sm" class="mb-3">Option 1 : Paiement direct</flux:heading>

                    <flux:field>
                        <flux:label>Numéro de téléphone</flux:label>
                        <flux:input 
                            type="tel" 
                            wire:model.live="phoneNumber" 
                            placeholder="Ex: 243970000000"
                            :disabled="$processing"
                        />
                        <flux:error name="phoneNumber" />
                    </flux:field>

                    <flux:field class="mt-3">
                        <flux:label>Opérateur Mobile Money</flux:label>
                        <flux:select wire:model.live="provider" :disabled="$processing">
                            <option value="">-- Sélectionner un opérateur --</option>
                            @foreach($this->providers as $code => $name)
                                <option value="{{ $code }}">{{ $name }}</option>
                            @endforeach
                        </flux:select>
                        <flux:error name="provider" />
                    </flux:field>

                    <flux:field class="mt-3">
                        <flux:label>Message optionnel (max 22 caractères)</flux:label>
                        <flux:input 
                            wire:model.live="customerMessage" 
                            placeholder="Note visible sur votre téléphone"
                            maxlength="22"
                            :disabled="$processing"
                        />
                        <flux:error name="customerMessage" />
                        @if($customerMessage)
                            <flux:text class="text-xs text-gray-500 mt-1">
                                {{ strlen($customerMessage) }}/22 caractères
                            </flux:text>
                        @endif
                    </flux:field>

                    <flux:button 
                        wire:click="initiateDeposit" 
                        variant="primary" 
                        class="w-full mt-4"
                        :disabled="$processing"
                    >
                        @if($processing)
                            <span wire:loading wire:target="initiateDeposit">
                                Traitement en cours...
                            </span>
                        @else
                            Payer directement
                        @endif
                    </flux:button>
                </div>

                {{-- Payment page option --}}
                <div class="border-t pt-4">
                    <flux:heading size="sm" class="mb-3">Option 2 : Page de paiement hébergée</flux:heading>
                    <flux:text class="text-sm text-gray-600 dark:text-gray-400 mb-3">
                        Vous serez redirigé vers une page sécurisée pour choisir votre opérateur et entrer votre numéro.
                    </flux:text>

                    <flux:button 
                        wire:click="initiatePaymentPage" 
                        variant="outline" 
                        class="w-full"
                        :disabled="$processing"
                    >
                        @if($processing)
                            <span wire:loading wire:target="initiatePaymentPage">
                                Redirection...
                            </span>
                        @else
                            Payer via page hébergée
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

    {{-- Auto-redirect to payment page if URL is set --}}
    @if($redirectUrl)
        <script>
            window.location.href = '{{ $redirectUrl }}';
        </script>
    @endif
</div>
