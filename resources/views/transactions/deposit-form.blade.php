<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-100 leading-tight">
            {{ $visitPass ? 'Payer le pass visite' : ($rentPayment ? 'Payer le loyer' : 'Dépôt') }}
        </h2>
    </x-slot>

    @if (session('error'))
        <div class="max-w-xl mx-auto sm:px-6 lg:px-8 mt-4">
            <div class="flex items-start gap-2 rounded-lg border border-red-200 dark:border-red-900 bg-red-50 dark:bg-red-900/20 p-4 text-sm text-red-700 dark:text-red-400">
                <i data-lucide="alert-circle" class="w-4 h-4 mt-0.5 shrink-0"></i>
                <span>{{ session('error') }}</span>
            </div>
        </div>
    @endif

    <div class="py-12">
        <div class="max-w-xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-xl border border-gray-100 dark:border-gray-700">
                <div class="p-6 sm:p-8">
                    <form method="POST" action="{{ route('transactions.deposit') }}" class="space-y-6">
                        @csrf

                        @include('transactions.partials.branding-header')

                        @if($visitPass)
                            <input type="hidden" name="visit_pass" value="{{ $visitPass->uuid }}">
                            <div class="flex items-center gap-3 rounded-xl bg-gray-50 dark:bg-gray-900/50 border border-gray-200 dark:border-gray-700 p-4">
                                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-primary/10">
                                    <i data-lucide="ticket" class="w-5 h-5 text-primary"></i>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Pass visite</p>
                                    <p class="text-sm text-gray-500 dark:text-gray-400 truncate">Réf. {{ $visitPass->reference }}</p>
                                </div>
                                <p class="text-lg font-semibold text-gray-900 dark:text-white whitespace-nowrap">
                                    {{ number_format($visitPass->amount, 0, ',', ' ') }} {{ config('services.pawapay.currency', 'XAF') }}
                                </p>
                                <input type="hidden" name="amount" value="{{ $visitPass->amount }}">
                            </div>
                        @elseif($rentPayment)
                            <input type="hidden" name="rent_payment" value="{{ $rentPayment->id }}">
                            <div class="flex items-center gap-3 rounded-xl bg-gray-50 dark:bg-gray-900/50 border border-gray-200 dark:border-gray-700 p-4">
                                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-primary/10">
                                    <i data-lucide="home" class="w-5 h-5 text-primary"></i>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Loyer</p>
                                    <p class="text-sm text-gray-500 dark:text-gray-400 truncate">Période {{ $rentPayment->month }}/{{ $rentPayment->year }}</p>
                                </div>
                                <p class="text-lg font-semibold text-gray-900 dark:text-white whitespace-nowrap">
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

                        <x-transactions.provider-picker :providers="$branding['providers']" />

                        <div>
                            <x-form.input
                                name="phone"
                                type="tel"
                                label="Numéro de téléphone"
                                placeholder="Entrez les 9 chiffres"
                                required
                                pattern="[0-9]{9}"
                                maxlength="9"
                                :prefix="'+'.$payment_config['prefix']"
                            />
                            <p class="mt-1.5 text-xs text-gray-500 dark:text-gray-400">
                                Le numéro associé à votre compte mobile money.
                            </p>
                        </div>

                        <div class="pt-2">
                            <x-btn type="submit" class="w-full">
                                {{ $visitPass ? 'Payer le pass' : ($rentPayment ? 'Payer le loyer' : 'Déposer') }}
                            </x-btn>
                            <p class="mt-3 flex items-center justify-center gap-1.5 text-xs text-gray-400 dark:text-gray-500">
                                <i data-lucide="lock" class="w-3.5 h-3.5"></i>
                                Paiement sécurisé via Mobile Money
                            </p>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>