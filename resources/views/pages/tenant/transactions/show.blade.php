<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <flux:heading size="xl">Détails de la transaction</flux:heading>
                <flux:subheading>{{ Str::limit($transaction->transaction_id, 12) }}</flux:subheading>
            </div>
            <flux:button href="{{ route('tenant.transactions.index') }}" variant="ghost" icon="arrow-left">
                Retour
            </flux:button>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <livewire:payment.transaction-status :transactionId="$transaction->transaction_id" />
        </div>
    </div>
</x-app-layout>
