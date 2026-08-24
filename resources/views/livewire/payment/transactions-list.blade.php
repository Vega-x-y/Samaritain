<div wire:poll.{{ $hasPendingTransactions ? $pollingInterval : 'keep-alive' }}ms>
    <flux:card>
        <div class="flex justify-between items-center mb-4">
            <flux:heading size="lg">Mes transactions</flux:heading>
            
            {{-- Filter by type --}}
            <flux:select wire:model.live="filterType" class="w-48">
                <option value="all">Toutes les transactions</option>
                <option value="deposits">Dépôts</option>
                <option value="payouts">Retraits</option>
                <option value="refunds">Remboursements</option>
            </flux:select>
        </div>

        @if($transactions->isEmpty())
            <div class="text-center py-8">
                <flux:text class="text-gray-500 dark:text-gray-400">
                    Aucune transaction trouvée.
                </flux:text>
            </div>
        @else
            {{-- Transactions table --}}
            <div class="overflow-x-auto">
                <flux:table>
                    <flux:table.head>
                        <flux:table.row>
                            <flux:table.cell class="font-semibold">ID</flux:table.cell>
                            <flux:table.cell class="font-semibold">Type</flux:table.cell>
                            <flux:table.cell class="font-semibold">Montant</flux:table.cell>
                            <flux:table.cell class="font-semibold">Statut</flux:table.cell>
                            <flux:table.cell class="font-semibold">Opérateur</flux:table.cell>
                            <flux:table.cell class="font-semibold">Date</flux:table.cell>
                            <flux:table.cell class="font-semibold">Actions</flux:table.cell>
                        </flux:table.row>
                    </flux:table.head>

                    <flux:table.body>
                        @foreach($transactions as $transaction)
                            <flux:table.row wire:key="transaction-{{ $transaction->transaction_id }}">
                                <flux:table.cell>
                                    <span class="text-xs font-mono text-gray-600 dark:text-gray-400">
                                        {{ Str::limit($transaction->transaction_id, 8, '') }}
                                    </span>
                                </flux:table.cell>
                                
                                <flux:table.cell>
                                    <flux:text class="text-sm">{{ $transaction->type->label() }}</flux:text>
                                </flux:table.cell>
                                
                                <flux:table.cell>
                                    <flux:text class="font-medium">
                                        {{ number_format($transaction->amount / 100, 0, ',', ' ') }} {{ $transaction->currency }}
                                    </flux:text>
                                </flux:table.cell>
                                
                                <flux:table.cell>
                                    <flux:badge variant="{{ $transaction->status->variant() }}" size="sm">
                                        {{ $transaction->status->label() }}
                                    </flux:badge>
                                </flux:table.cell>
                                
                                <flux:table.cell>
                                    @if($transaction->provider)
                                        <flux:text class="text-sm">
                                            {{ config('pawapay.providers.' . $transaction->provider, $transaction->provider) }}
                                        </flux:text>
                                    @else
                                        <span class="text-gray-400 text-sm">—</span>
                                    @endif
                                </flux:table.cell>
                                
                                <flux:table.cell>
                                    <flux:text class="text-sm text-gray-600 dark:text-gray-400">
                                        {{ $transaction->created_at->format('d/m/Y H:i') }}
                                    </flux:text>
                                </flux:table.cell>
                                
                                <flux:table.cell>
                                    <a href="{{ route('tenant.transactions.show', $transaction) }}" wire:navigate>
                                        <flux:button variant="ghost" size="sm">
                                            Détails
                                        </flux:button>
                                    </a>
                                </flux:table.cell>
                            </flux:table.row>
                        @endforeach
                    </flux:table.body>
                </flux:table>
            </div>

            {{-- Pagination --}}
            <div class="mt-4">
                {{ $transactions->links() }}
            </div>
        @endif

        {{-- Polling indicator --}}
        @if($hasPendingTransactions)
            <div class="mt-4 flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
                <flux:icon icon="arrow-path" class="w-4 h-4 animate-spin" />
                <flux:text>Actualisation automatique toutes les 10 secondes...</flux:text>
            </div>
        @endif
    </flux:card>
</div>
