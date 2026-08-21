@extends('layouts.base')

@section('title', 'Statut du paiement')

@section('content')
<div class="max-w-xl mx-auto px-6 py-12">
    <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-6 shadow-sm">
        <div class="flex items-center gap-3">
            <i data-lucide="wallet-cards" class="h-6 w-6 text-primary"></i>
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Statut du paiement</h1>
        </div>
        @if(isset($transaction))
            <p class="mt-4 text-sm text-gray-600 dark:text-gray-300">Transaction : {{ $transaction->transaction_id }}</p>
            <p class="mt-2 text-sm text-gray-600 dark:text-gray-300">Statut : {{ ucfirst($transaction->status) }}</p>
        @elseif(isset($provider))
            <p class="mt-4 text-sm text-gray-600 dark:text-gray-300">Opérateur détecté : {{ $provider }}</p>
        @else
            <p class="mt-4 text-sm text-gray-600 dark:text-gray-300">Aucune transaction à afficher.</p>
        @endif
    </div>
</div>
@endsection