@extends('layouts.base')

@section('title', 'Statut du paiement')

@section('content')
    <div class="max-w-xl mx-auto px-6 py-12">
        @if (session('success'))
            <div class="alert alert-success" role="alert">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger" role="alert">
                {{ session('error') }}
            </div>
        @endif

        <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Statut du paiement</h1>
        <dl class="mt-6 space-y-3 text-sm text-gray-600 dark:text-gray-300">
            <div class="flex justify-between gap-4"><dt>Client</dt><dd class="font-medium text-gray-900 dark:text-white">{{ $transaction->user->name }}</dd></div>
            <div class="flex justify-between gap-4"><dt>Montant</dt><dd class="font-medium text-gray-900 dark:text-white">{{ number_format($transaction->amount, 0, ',', ' ') }} {{ $transaction->currency ?? config('services.pawapay.currency', 'XAF') }}</dd></div>
            <div class="flex justify-between gap-4"><dt>Statut</dt><dd class="font-medium text-gray-900 dark:text-white">{{ ucfirst($transaction->status) }}</dd></div>
            <div class="flex justify-between gap-4"><dt>Référence</dt><dd class="font-mono text-xs text-gray-900 dark:text-white">{{ $transaction->transaction_id }}</dd></div>
        </dl>
    </div>
@endsection