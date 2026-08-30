@extends('layouts.base')

@section('title', 'Mes Transactions')

@section('content')
    <x-ui.user-dashboard-nav />
    <x-blade-components::layout.container>
        <div class="container mx-auto px-4 py-8">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-8">
                <div>
                    <h1 class="text-xl md:text-2xl font-bold text-gray-900 dark:text-white">Mes <span class="text-orange-500">Transactions</span></h1>
                    <p class="text-gray-500 dark:text-gray-400 mt-1 text-sm">Tous vos paiements effectués sur la plateforme</p>
                </div>
                <a href="{{ route('client.dashboard') }}" class="px-4 py-2 rounded-full text-sm font-medium border border-gray-300 text-gray-600 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-400 dark:hover:bg-gray-800 transition">
                    ← Retour au dashboard
                </a>
            </div>

            {{-- Statistiques rapides --}}
            <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-6">
                <div class="bg-white dark:bg-gray-800 rounded-xl p-4 border border-gray-100 dark:border-gray-700 shadow-sm">
                    <p class="text-xs text-gray-500 dark:text-gray-400">Total dépensé</p>
                    <p class="text-xl font-bold text-gray-900 dark:text-white mt-1">
                        {{ number_format($stats['total_spent'], 0, ',', ' ') }} FCFA
                    </p>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-xl p-4 border border-gray-100 dark:border-gray-700 shadow-sm">
                    <p class="text-xs text-gray-500 dark:text-gray-400">Transactions</p>
                    <p class="text-xl font-bold text-gray-900 dark:text-white mt-1">{{ $stats['total_count'] }}</p>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-xl p-4 border border-gray-100 dark:border-gray-700 shadow-sm">
                    <p class="text-xs text-gray-500 dark:text-gray-400">Complétées</p>
                    <p class="text-xl font-bold text-emerald-600 dark:text-emerald-400 mt-1">{{ $stats['completed_count'] }}</p>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-xl p-4 border border-gray-100 dark:border-gray-700 shadow-sm">
                    <p class="text-xs text-gray-500 dark:text-gray-400">En attente</p>
                    <p class="text-xl font-bold text-amber-500 dark:text-amber-400 mt-1">{{ $stats['pending_count'] }}</p>
                </div>
            </div>

            {{-- Liste des transactions --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700">
                    <h2 class="font-semibold text-gray-800 dark:text-white">Historique</h2>
                </div>

                @if($transactions->isEmpty())
                    <div class="py-16 text-center">
                        <i data-lucide="receipt" class="w-12 h-12 text-gray-300 dark:text-gray-600 mx-auto mb-3"></i>
                        <p class="text-gray-500 dark:text-gray-400">Aucune transaction pour l'instant.</p>
                    </div>
                @else
                    <div class="divide-y divide-gray-100 dark:divide-gray-700">
                        @foreach($transactions as $transaction)
                            @php
                                $label = match(true) {
                                    (bool) $transaction->artisan_request_id => 'Acompte Artisan',
                                    (bool) $transaction->visit_pass_id => 'Pass Visite',
                                    (bool) $transaction->rent_payment_id => 'Loyer',
                                    default => 'Dépôt wallet',
                                };
                                $icon = match(true) {
                                    (bool) $transaction->artisan_request_id => 'hard-hat',
                                    (bool) $transaction->visit_pass_id => 'ticket',
                                    (bool) $transaction->rent_payment_id => 'home',
                                    default => 'wallet',
                                };
                                $colorClass = match(true) {
                                    (bool) $transaction->artisan_request_id => 'bg-orange-100 dark:bg-orange-900/30 text-orange-600 dark:text-orange-400',
                                    (bool) $transaction->visit_pass_id => 'bg-purple-100 dark:bg-purple-900/30 text-purple-600 dark:text-purple-400',
                                    (bool) $transaction->rent_payment_id => 'bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400',
                                    default => 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400',
                                };
                                $statusLabel = match($transaction->status?->value ?? $transaction->status) {
                                    'COMPLETED' => ['label' => 'Complété', 'class' => 'bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400'],
                                    'PENDING' => ['label' => 'En attente', 'class' => 'bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400'],
                                    'FAILED' => ['label' => 'Échoué', 'class' => 'bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400'],
                                    default => ['label' => 'Inconnu', 'class' => 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400'],
                                };
                            @endphp
                            <div class="px-6 py-4 flex items-center justify-between hover:bg-gray-50 dark:hover:bg-gray-700/40 transition">
                                <div class="flex items-center gap-4">
                                    <div class="w-10 h-10 rounded-full flex items-center justify-center shrink-0 {{ $colorClass }}">
                                        <i data-lucide="{{ $icon }}" class="w-5 h-5"></i>
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-gray-800 dark:text-white">{{ $label }}</p>
                                        <p class="text-xs text-gray-400 dark:text-gray-500">
                                            {{ $transaction->created_at->format('d/m/Y à H:i') }}
                                            · {{ strtoupper($transaction->provider ?? '—') }}
                                        </p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-4">
                                    <span class="text-xs px-2 py-1 rounded-full font-medium {{ $statusLabel['class'] }}">
                                        {{ $statusLabel['label'] }}
                                    </span>
                                    <p class="text-sm font-bold text-gray-800 dark:text-white text-right min-w-[100px]">
                                        {{ number_format($transaction->amount, 0, ',', ' ') }} FCFA
                                    </p>
                                    @if(($transaction->status?->value ?? $transaction->status) === 'PENDING')
                                        <a href="{{ route('transactions.deposit.status', $transaction) }}"
                                           class="text-xs text-orange-600 dark:text-orange-400 hover:underline">
                                            Vérifier →
                                        </a>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>

                    @if($transactions->hasPages())
                        <div class="px-6 py-4 border-t border-gray-100 dark:border-gray-700">
                            {{ $transactions->links() }}
                        </div>
                    @endif
                @endif
            </div>
        </div>
    </x-blade-components::layout.container>
@endsection
