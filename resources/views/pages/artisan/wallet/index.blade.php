@extends('layouts.artisan')

@section('title', 'Mon Wallet - Artisan')

@section('breadcrumbs')
    <nav class="flex items-center gap-2 text-sm text-muted-foreground">
        <i data-lucide="piggy-bank" class="w-4 h-4"></i>
        <span class="text-foreground font-medium">Mon Wallet</span>
    </nav>
@endsection

@section('content')
<div class="container mx-auto px-4 py-8">
    @if(session('success'))
        <div class="bg-emerald-50 dark:bg-emerald-900/30 border border-emerald-200 dark:border-emerald-700 text-emerald-800 dark:text-emerald-300 rounded-lg p-4 mb-6 flex items-center gap-3">
            <i data-lucide="check-circle" class="w-5 h-5 shrink-0"></i>
            {{ session('success') }}
        </div>
    @endif

    {{-- Solde --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-5 mb-8">
        <div class="md:col-span-2 bg-gradient-to-br from-orange-500 to-orange-600 rounded-2xl p-6 text-white shadow-lg">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-orange-100 text-sm font-medium mb-1">Solde disponible</p>
                    <p class="text-4xl font-bold">{{ number_format($wallet->available_balance, 0, ',', ' ') }}</p>
                    <p class="text-orange-200 text-sm mt-1">FCFA</p>
                </div>
                <div class="bg-white/20 rounded-xl p-3">
                    <i data-lucide="wallet" class="w-7 h-7"></i>
                </div>
            </div>
            @if($wallet->reserved_balance > 0)
                <div class="mt-4 pt-4 border-t border-white/20 text-sm text-orange-100">
                    <span>En réserve : <strong class="text-white">{{ number_format($wallet->reserved_balance, 0, ',', ' ') }} FCFA</strong></span>
                </div>
            @endif
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 border border-gray-100 dark:border-gray-700 shadow-sm flex flex-col justify-between">
            <div>
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Retraits</p>
                <p class="text-2xl font-bold text-gray-800 dark:text-white">
                    {{ number_format(abs($entries->where('kind', 'payout')->sum('amount')), 0, ',', ' ') }} FCFA
                </p>
                <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">Total retiré</p>
            </div>
            <a href="{{ route('artisan.wallet.withdraw.form') }}"
               class="mt-4 block text-center bg-gray-900 hover:bg-gray-700 dark:bg-white dark:hover:bg-gray-100 text-white dark:text-gray-900 font-medium py-2.5 px-4 rounded-lg transition text-sm">
                <span class="flex items-center justify-center gap-2">
                    <i data-lucide="arrow-up-from-line" class="w-4 h-4"></i>
                    Retirer des fonds
                </span>
            </a>
        </div>
    </div>

    {{-- Historique --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm">
        <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
            <h2 class="font-semibold text-gray-800 dark:text-white">Historique des mouvements</h2>
            <i data-lucide="clock" class="w-5 h-5 text-gray-400 dark:text-gray-500"></i>
        </div>

        @if($entries->isEmpty())
            <div class="py-16 text-center">
                <i data-lucide="piggy-bank" class="w-12 h-12 text-gray-300 dark:text-gray-600 mx-auto mb-3"></i>
                <p class="text-gray-500 dark:text-gray-400">Aucun mouvement pour l'instant.</p>
                <p class="text-sm text-gray-400 dark:text-gray-500 mt-1">Les acomptes reçus de vos clients apparaîtront ici.</p>
            </div>
        @else
            <div class="divide-y divide-gray-100 dark:divide-gray-700">
                @foreach($entries as $entry)
                    <div class="px-6 py-4 flex items-center justify-between hover:bg-gray-50 dark:hover:bg-gray-700/40 transition">
                        <div class="flex items-center gap-4">
                            <div class="w-10 h-10 rounded-full flex items-center justify-center shrink-0
                                {{ $entry->kind === 'deposit' ? 'bg-emerald-100 dark:bg-emerald-900/30' : 'bg-red-100 dark:bg-red-900/30' }}">
                                <i data-lucide="{{ $entry->kind === 'deposit' ? 'arrow-down-to-line' : 'arrow-up-from-line' }}"
                                   class="w-5 h-5 {{ $entry->kind === 'deposit' ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-600 dark:text-red-400' }}"></i>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-800 dark:text-white">
                                    {{ $entry->kind === 'deposit' ? 'Acompte reçu' : 'Retrait' }}
                                </p>
                                @if($entry->kind === 'deposit' && isset($entry->metadata['commission_amount']))
                                    <p class="text-xs text-gray-400 dark:text-gray-500">
                                        Commission déduite : {{ number_format($entry->metadata['commission_amount'], 0, ',', ' ') }} FCFA
                                        ({{ $entry->metadata['commission_percent'] ?? '?' }}%)
                                    </p>
                                @endif
                                <p class="text-xs text-gray-400 dark:text-gray-500">
                                    {{ $entry->created_at->format('d/m/Y à H:i') }}
                                </p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="text-sm font-bold {{ $entry->amount >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-600 dark:text-red-400' }}">
                                {{ $entry->amount >= 0 ? '+' : '' }}{{ number_format($entry->amount, 0, ',', ' ') }} FCFA
                            </p>
                            @if($entry->kind === 'deposit' && isset($entry->metadata['original_amount']))
                                <p class="text-xs text-gray-400 dark:text-gray-500">
                                    Brut : {{ number_format($entry->metadata['original_amount'], 0, ',', ' ') }} FCFA
                                </p>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>

            @if($entries->hasPages())
                <div class="px-6 py-4 border-t border-gray-100 dark:border-gray-700">
                    {{ $entries->links() }}
                </div>
            @endif
        @endif
    </div>
</div>
@endsection
