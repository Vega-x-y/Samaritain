@extends('layouts.base')

@section('title', 'Paiement en cours')

@section('content')
@php
    use App\Enums\TransactionStatus;
    
    $isCompleted = $transaction->status === TransactionStatus::COMPLETED
        || ($transaction->visitPass && $transaction->visitPass->isPaid())
        || ($transaction->rentPayment && $transaction->rentPayment->isPaid());

    $isFailed = in_array($transaction->status, [TransactionStatus::FAILED, TransactionStatus::REJECTED], true)
        || ($transaction->visitPass && $transaction->visitPass->isPaymentFailed());

    $providerStatus = strtoupper((string) data_get($transaction->raw_response, 'status', $transaction->status->value ?? 'PENDING'));
    $isReconciliation = $providerStatus === 'IN_RECONCILIATION';

    $currency = $transaction->currency ?? config('services.pawapay.currency', 'XAF');

    $successRoute = null;
    if ($transaction->visit_pass_id && $transaction->visitPass) {
        $successRoute = route('my-visit-passes.show', $transaction->visitPass);
    } elseif ($transaction->rent_payment_id) {
        $successRoute = route('tenant.payments');
    }

    $retryRoute = null;
    if ($transaction->visit_pass_id && $transaction->visitPass) {
        $retryRoute = route('transactions.deposit', ['visit_pass' => $transaction->visitPass->uuid]);
    } elseif ($transaction->rent_payment_id && $transaction->rentPayment) {
        $retryRoute = route('tenant.rent-payments.pay', $transaction->rentPayment);
    }
@endphp

<div class="font-body bg-background dark:bg-gray-950 text-[#0F0E0C] dark:text-white antialiased min-h-screen">
    <div class="max-w-xl mx-auto px-6 py-16 text-center">

        @if ($isCompleted)
            <div class="mx-auto mb-6 w-16 h-16 rounded-full bg-emerald-100 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400 flex items-center justify-center">
                <i data-lucide="check-circle" class="w-8 h-8"></i>
            </div>
            <h1 class="font-display font-semibold text-2xl mb-3">Paiement confirmé</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">
                {{ $transaction->visit_pass_id ? 'Votre pass visite est disponible.' : 'Votre paiement de loyer a bien été pris en compte.' }}
            </p>
            @if ($successRoute)
                <a href="{{ $successRoute }}"
                    class="inline-flex items-center justify-center gap-2 rounded-xl bg-primary text-white px-6 py-3 text-sm font-semibold hover:bg-primary/90 transition-colors">
                    <i data-lucide="arrow-right" class="w-4 h-4"></i>
                    Continuer
                </a>
            @endif

        @elseif ($isFailed)
            <div class="w-16 h-16 mx-auto mb-6 rounded-full bg-amber-100 dark:bg-amber-900/30 text-amber-600 dark:text-amber-400 flex items-center justify-center">
                <i data-lucide="alert-triangle" class="w-8 h-8"></i>
            </div>
            <h1 class="font-display font-semibold text-2xl mb-3">Le paiement n'a pas abouti</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">
                La transaction n'a pas été confirmée. Vous pouvez réessayer avec un autre opérateur ou un autre numéro.
            </p>
            @if ($retryRoute)
                <a href="{{ $retryRoute }}"
                    class="inline-flex items-center justify-center gap-2 rounded-xl bg-primary text-white px-6 py-3 text-sm font-semibold hover:bg-primary/90 transition-colors">
                    <i data-lucide="refresh-cw" class="w-4 h-4"></i>
                    Réessayer le paiement
                </a>
            @endif

        @else
            <div class="relative w-16 h-16 mx-auto mb-6">
                <span class="absolute inset-0 rounded-full bg-primary/20 animate-ping"></span>
                <span class="relative flex h-16 w-16 items-center justify-center rounded-full bg-primary/10 text-primary">
                    <i data-lucide="smartphone" class="w-8 h-8"></i>
                </span>
            </div>
            <h1 class="font-display font-semibold text-2xl mb-3">Validez depuis votre téléphone</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">
                @if ($isReconciliation)
                    pawaPay vérifie encore cette transaction. Le statut final sera actualisé automatiquement.
                @else
                    Votre paiement de {{ number_format($transaction->amount / 100, 0, ',', ' ') }} {{ $currency }} est en cours de confirmation par pawaPay.
                    Le statut sera vérifié automatiquement après votre retour de la page de paiement.
                @endif
            </p>
            <a href="{{ route('transactions.status', $transaction) }}"
                class="inline-flex items-center justify-center gap-2 rounded-xl border border-gray-200 dark:border-gray-600 px-6 py-3 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                <i data-lucide="refresh-cw" class="w-4 h-4"></i>
                Vérifier le statut
            </a>
            <p class="text-xs text-gray-400 dark:text-gray-500 mt-4">Référence : {{ $transaction->transaction_id }}</p>

            <script>
                (function () {
                    setTimeout(function () {
                        window.location.href = "{{ route('transactions.status', $transaction) }}";
                    }, 5000);
                })();
            </script>
        @endif

    </div>
</div>
@endsection