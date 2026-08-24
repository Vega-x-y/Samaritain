<x-app-layout>
    <x-slot name="header">
        <flux:heading size="xl">Mes transactions</flux:heading>
        <flux:subheading>Historique de vos paiements Mobile Money</flux:subheading>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <livewire:payment.transactions-list />
        </div>
    </div>
</x-app-layout>
