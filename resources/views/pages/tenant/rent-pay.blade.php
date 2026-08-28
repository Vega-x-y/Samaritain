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
    <p class="text-gray-500 dark:text-gray-400 mt-1">Saisissez vos coordonnées Mobile Money pour confirmer le paiement.</p>
</div>

<div class="grid grid-cols-1 lg:grid-cols-[1fr_340px] gap-8 items-start">
    <form action="{{ route('tenant.rent-payments.initiate', $rentPayment) }}" method="POST"
        class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-sm border border-gray-100 dark:border-gray-700">
        @csrf

        <div>
            <h2 class="font-semibold text-gray-800 dark:text-white">Confirmer le paiement</h2>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                Le paiement sera directement initié auprès de votre opérateur Mobile Money.
            </p>
        </div>

        <div class="mt-6 space-y-4">
            <input type="text" name="phone_number" required placeholder="Numéro Mobile Money"
                class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
            <select name="provider" required
                class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                <option value="">Choisir l'opérateur</option>
                @foreach($providers as $code => $label)
                    <option value="{{ $code }}">{{ $label }}</option>
                @endforeach
            </select>
        </div>

        <div class="mt-6 flex flex-col sm:flex-row items-center gap-3">
            <button type="submit"
                class="inline-flex w-full sm:w-auto items-center justify-center gap-2 rounded-xl bg-emerald-600 text-white px-6 py-3 text-sm font-semibold hover:bg-emerald-700 transition-colors">
                <i data-lucide="wallet" class="w-4 h-4"></i>
                Payer {{ number_format($rentPayment->amount_due, 0, ',', ' ') }} {{ $currency }}
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