@extends('layouts.tenant')

@section('title', 'Payer le loyer')

@section('content')
<div class="mb-6">
    <nav class="flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400 mb-4">
        <a href="{{ route('tenant.payments') }}" class="hover:text-gray-800 dark:hover:text-white transition-colors">Mes Paiements</a>
        <i data-lucide="chevron-right" class="w-3 h-3"></i>
        <span class="dark:text-gray-300">Payer le loyer</span>
    </nav>
    <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Payer le loyer</h1>
    <p class="text-gray-500 dark:text-gray-400 mt-1">Choisissez votre opérateur Mobile Money et renseignez votre numéro. Vous validerez depuis votre téléphone.</p>
</div>

@if ($errors->any())
    <div class="mb-6 p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-xl text-red-700 dark:text-red-400 text-sm">
        <div class="flex items-start gap-2">
            <i data-lucide="alert-circle" class="w-4 h-4 mt-0.5 shrink-0"></i>
            <ul class="space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    </div>
@endif

<div class="grid grid-cols-1 lg:grid-cols-[1fr_340px] gap-8 items-start">
    <form action="{{ route('tenant.rent-payments.initiate', $rentPayment) }}" method="POST"
        class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-sm border border-gray-100 dark:border-gray-700">
        @csrf

        <h2 class="font-semibold text-gray-800 dark:text-white text-lg mb-4">Votre opérateur</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
            @foreach ($providers as $code => $provider)
                <label class="relative block cursor-pointer rounded-xl border-2 p-5 transition has-[:checked]:border-emerald-500 has-[:checked]:bg-emerald-50 dark:has-[:checked]:bg-emerald-900/20 border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 hover:border-gray-300 dark:hover:border-gray-600">
                    <input type="radio" name="provider" value="{{ $code }}"
                        class="peer sr-only" required
                        @checked(old('provider') === $code)>
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-lg bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-600 flex items-center justify-center p-1 shrink-0">
                                @if (!empty($provider['logo']))
                                    <img src="{{ $provider['logo'] }}" alt="{{ $provider['label'] }}"
                                        class="w-full h-full object-contain">
                                @else
                                    <i data-lucide="smartphone" class="w-5 h-5 text-emerald-600 dark:text-emerald-400"></i>
                                @endif
                            </div>
                            <div>
                                <p class="font-semibold text-sm text-gray-800 dark:text-white">{{ $provider['label'] }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ $code }}</p>
                            </div>
                        </div>
                        <span class="h-5 w-5 rounded-full border-2 border-gray-300 dark:border-gray-600 peer-checked:border-emerald-500 peer-checked:bg-emerald-500"></span>
                    </div>
                </label>
            @endforeach
        </div>

        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2" for="phone">
            Numéro Mobile Money <span class="text-xs text-gray-400">(avec ou sans +, ex. +24206000000)</span>
        </label>
        <input type="tel" id="phone" name="phone" value="{{ old('phone') }}" required inputmode="tel"
            placeholder="+242 06 000 00 00"
            class="w-full rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500" />

        <div class="mt-6 flex flex-col sm:flex-row items-center gap-3">
            <button type="submit"
                class="inline-flex w-full sm:w-auto items-center justify-center gap-2 rounded-xl bg-emerald-600 text-white px-6 py-3 text-sm font-semibold hover:bg-emerald-700 transition-colors">
                <i data-lucide="wallet" class="w-4 h-4"></i>
                Valider le paiement ({{ number_format($rentPayment->amount_due, 0, ',', ' ') }} {{ $currency }})
            </button>
            <a href="{{ route('tenant.payments') }}"
                class="inline-flex w-full sm:w-auto items-center justify-center gap-2 rounded-xl border border-gray-200 dark:border-gray-600 px-6 py-3 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                Annuler
            </a>
        </div>
    </form>

    <div class="lg:sticky lg:top-8">
        <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-sm border border-gray-100 dark:border-gray-700">
            <h3 class="font-semibold text-sm text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-4">Récapitulatif</h3>
            <div class="flex justify-between items-center mb-2 text-sm">
                <span class="text-gray-600 dark:text-gray-400">Loyer : {{ $contract->tenant_name ?? '—' }}</span>
                <span class="font-medium text-gray-800 dark:text-white">Période {{ $rentPayment->month }}/{{ $rentPayment->year }}</span>
            </div>
            <div class="flex justify-between items-center border-t border-gray-100 dark:border-gray-700 pt-3 text-sm">
                <span class="text-gray-600 dark:text-gray-400">Montant dû</span>
                <span class="font-semibold text-gray-900 dark:text-white">{{ number_format($rentPayment->amount_due, 0, ',', ' ') }} {{ $currency }}</span>
            </div>
        </div>
    </div>
</div>
@endsection