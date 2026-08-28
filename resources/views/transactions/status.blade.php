<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Statut de la transaction
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-xl border border-gray-100">
                <div class="p-8 text-gray-900">
                    @if ($transaction->is_pending)
                        <div class="text-center">
                            <div class="mx-auto mb-5 flex h-16 w-16 items-center justify-center rounded-full bg-yellow-50">
                                <svg class="h-8 w-8 text-yellow-500 animate-pulse" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <circle cx="12" cy="12" r="10" stroke-width="2" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 8v4m0 4h.01" />
                                </svg>
                            </div>
                            @if ($transaction->type->value === 'DEPOSIT')
                                <h3 class="text-lg font-semibold mb-2">Paiement en attente</h3>
                                <p class="mb-6 text-gray-600">Veuillez valider la transaction sur votre téléphone.
                                    Une fois terminé, cliquez sur le bouton ci-dessous pour vérifier votre paiement.
                                </p>
                                <x-btn href="{{ route('transactions.deposit.status', $transaction) }}">
                                    Vérifier le paiement
                                </x-btn>
                            @else
                                <h3 class="text-lg font-semibold mb-2">Transfert en attente</h3>
                                <p class="mb-6 text-gray-600">Votre retrait est en cours de traitement, vous
                                    recevrez une notification une fois le transfert terminé. Vous pouvez actualiser
                                    cette page pour vérifier le statut de votre retrait.</p>
                                <x-btn href="{{ route('transactions.withdraw.status', $transaction) }}">
                                    Vérifier le paiement
                                </x-btn>
                            @endif
                        </div>
                    @elseif($transaction->is_failed)
                        <div class="text-center">
                            <div class="mx-auto mb-5 flex h-16 w-16 items-center justify-center rounded-full bg-red-50">
                                <svg class="h-8 w-8 text-red-500" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <circle cx="12" cy="12" r="10" stroke-width="2" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 9l-6 6m0-6l6 6" />
                                </svg>
                            </div>
                            <h3 class="text-lg font-semibold mb-2 text-red-600">Échec de la transaction</h3>
                            <p class="mb-2 text-gray-600">Une erreur est survenue lors de votre transaction.</p>
                            @if ($transaction->failure_reason)
                                <p class="mb-6 text-sm text-gray-500 bg-gray-50 rounded-lg px-3 py-2 inline-block">
                                    Cause : {{ $transaction->failure_reason }}
                                </p>
                            @endif

                            <div>
                                @if ($transaction->visit_pass_id && $transaction->visitPass)
                                    <x-btn style="warning"
                                        href="{{ route('transactions.deposit', ['visit_pass' => $transaction->visitPass->uuid]) }}">
                                        Réessayer le paiement
                                    </x-btn>
                                @elseif ($transaction->rent_payment_id && $transaction->rentPayment)
                                    <x-btn style="warning"
                                        href="{{ route('transactions.deposit', ['rent_payment' => $transaction->rentPayment->id]) }}">
                                        Réessayer le paiement
                                    </x-btn>
                                @else
                                    <x-btn style="warning" href="{{ route('transactions.deposit') }}">
                                        Réessayer une autre transaction
                                    </x-btn>
                                @endif
                            </div>
                        </div>
                    @elseif($transaction->is_completed)
                        <div class="text-center">
                            <div class="mx-auto mb-5 flex h-16 w-16 items-center justify-center rounded-full bg-green-50">
                                <svg class="h-8 w-8 text-green-500" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <circle cx="12" cy="12" r="10" stroke-width="2" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 12l2 2 4-4" />
                                </svg>
                            </div>
                            @if ($transaction->visit_pass_id && $transaction->visitPass)
                                <h3 class="text-lg font-semibold mb-2 text-green-600">Paiement réussi</h3>
                                <p class="mb-6 text-gray-600">Votre pass visite a été validé avec succès.</p>
                                <x-btn style="success" href="{{ route('my-visit-passes.show', $transaction->visitPass) }}">
                                    Voir mon pass
                                </x-btn>
                            @elseif ($transaction->rent_payment_id && $transaction->rentPayment)
                                <h3 class="text-lg font-semibold mb-2 text-green-600">Loyer payé</h3>
                                <p class="mb-6 text-gray-600">
                                    Votre loyer ({{ $transaction->rentPayment->month }}/{{ $transaction->rentPayment->year }})
                                    a été payé avec succès. Un reçu a été généré.
                                </p>
                                <x-btn style="success" href="{{ route('tenant.payments') }}">
                                    Voir mes paiements
                                </x-btn>
                            @elseif ($transaction->type->value === 'DEPOSIT')
                                <h3 class="text-lg font-semibold mb-2 text-green-600">Paiement réussi</h3>
                                <p class="text-gray-600">Votre paiement a été validé avec succès et votre
                                    portefeuille a été crédité.</p>
                            @else
                                <h3 class="text-lg font-semibold mb-2 text-green-600">Transfert réussi</h3>
                                <p class="text-gray-600">Votre retrait a été traité avec succès et l'argent a été
                                    transféré vers votre compte mobile money.</p>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>