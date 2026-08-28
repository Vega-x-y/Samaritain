<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ $visitPass ? 'Payer le pass visite' : ($rentPayment ? 'Payer le loyer' : 'Dépôt') }}
        </h2>
    </x-slot>

    @if (session('error'))
        <div class="max-w-xl mx-auto sm:px-6 lg:px-8 mt-4">
            <div class="rounded-lg border border-red-200 dark:border-red-900 bg-red-50 dark:bg-red-900/20 p-4 text-sm text-red-700 dark:text-red-400">
                {{ session('error') }}
            </div>
        </div>
    @endif

    <div class="py-12">
        <div class="max-w-xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-xl border border-gray-100 dark:border-gray-700">
                <div class="p-6 sm:p-8">
                    <form method="POST" action="{{ route('transactions.deposit') }}" class="space-y-5">
                        @csrf

                        @include('transactions.partials.branding-header')

                        @if($visitPass)
                            <input type="hidden" name="visit_pass" value="{{ $visitPass->uuid }}">
                            <div class="rounded-xl bg-gray-50 dark:bg-gray-900/50 border border-gray-200 dark:border-gray-700 p-4">
                                <p class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Pass visite</p>
                                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Réf. {{ $visitPass->reference }}</p>
                                <p class="mt-2 text-lg font-semibold text-gray-900 dark:text-white">
                                    {{ number_format($visitPass->amount, 0, ',', ' ') }} {{ config('services.pawapay.currency', 'XAF') }}
                                </p>
                                <input type="hidden" name="amount" value="{{ $visitPass->amount }}">
                            </div>
                        @elseif($rentPayment)
                            <input type="hidden" name="rent_payment" value="{{ $rentPayment->id }}">
                            <div class="rounded-xl bg-gray-50 dark:bg-gray-900/50 border border-gray-200 dark:border-gray-700 p-4">
                                <p class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Loyer</p>
                                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Période {{ $rentPayment->month }}/{{ $rentPayment->year }}</p>
                                <p class="mt-2 text-lg font-semibold text-gray-900 dark:text-white">
                                    {{ number_format($rentPayment->amount_due, 0, ',', ' ') }} {{ config('services.pawapay.currency', 'XAF') }}
                                </p>
                                <input type="hidden" name="amount" value="{{ $rentPayment->amount_due }}">
                            </div>
                        @else
                            <x-form.input
                                name="amount"
                                type="number"
                                label="Montant"
                                icon="banknote"
                                placeholder="0"
                                required
                                min="1"
                                :suffix="config('services.pawapay.currency', 'XAF')"
                            />
                        @endif

                        @include('transactions.partials.provider-picker')

                        <div>
                            <x-form.input
                                name="phone"
                                type="tel"
                                label="Numéro de téléphone"
                                icon="phone"
                                placeholder="Entrez les 9 chiffres"
                                required
                                pattern="[0-9]{9}"
                                maxlength="9"
                                :prefix="'+'.$payment_config['prefix']"
                            />
                            <p class="-mt-3 text-xs text-gray-500 dark:text-gray-400">Entrez les 9 chiffres après +{{ $payment_config['prefix'] }}</p>
                        </div>

                        <div class="pt-2">
                            <x-btn type="submit" class="w-full">
                                {{ $visitPass ? 'Payer le pass' : ($rentPayment ? 'Payer le loyer' : 'Déposer') }}
                            </x-btn>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>