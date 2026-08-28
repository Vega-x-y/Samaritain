<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Retrait d'argent
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
                    <form method="POST" action="{{ route('transactions.withdraw') }}" class="space-y-5">
                        @csrf

                        @include('transactions.partials.branding-header')
                        
                        <p class="-mt-3 text-xs text-gray-500 dark:text-gray-400">
                            Solde disponible : {{ number_format($balance, 0, ',', ' ') }} {{ config('services.pawapay.currency', 'XAF') }}
                        </p>

                        <div>
                            <x-form.input
                                name="amount"
                                type="number"
                                label="Montant"
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
                        </div>

                        <div class="pt-2">
                            <x-btn type="submit" class="w-full">
                                Retirer
                            </x-btn>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>