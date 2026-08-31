<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-100 leading-tight">
            Retrait d'argent
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
                    <form method="POST" action="{{ $action ?? route('transactions.withdraw') }}" class="space-y-6">
                        @csrf

                        @include('transactions.partials.branding-header')

                        <div class="flex items-center justify-between rounded-xl bg-gray-50 dark:bg-gray-900/50 border border-gray-200 dark:border-gray-700 px-4 py-3">
                            <div class="flex items-center gap-2">
                                <i data-lucide="wallet" class="w-4 h-4 text-gray-400 dark:text-gray-500"></i>
                                <span class="text-xs text-gray-500 dark:text-gray-400">Solde disponible</span>
                            </div>
                            <span class="text-sm font-semibold text-gray-900 dark:text-white">
                                {{ number_format($balance, 0, ',', ' ') }} {{ config('services.pawapay.currency', 'XAF') }}
                            </span>
                        </div>

                        <div>
                            <div class="flex items-center justify-between mb-1.5">
                                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300">Montant</label>
                                <button type="button"
                                    onclick="document.querySelector('input[name=amount]').value = {{ (int) $balance }}"
                                    class="text-xs font-medium text-primary hover:underline">
                                    Retirer tout
                                </button>
                            </div>
                            <x-form.input
                                name="amount"
                                type="number"
                                icon="banknote"
                                placeholder="Montant à retirer"
                                required
                                min="10"
                                max="{{ $balance }}"
                                :suffix="config('services.pawapay.currency', 'XAF')"
                            />
                        </div>

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
                                Le numéro qui recevra les fonds.
                            </p>
                        </div>

                        <div class="pt-2">
                            <x-btn type="submit" class="w-full">
                                Retirer
                            </x-btn>
                            <p class="mt-3 flex items-center justify-center gap-1.5 text-xs text-gray-400 dark:text-gray-500">
                                <i data-lucide="lock" class="w-3.5 h-3.5"></i>
                                Transfert sécurisé via Mobile Money
                            </p>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>