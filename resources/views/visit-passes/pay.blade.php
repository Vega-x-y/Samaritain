@extends('layouts.base')

@section('title', 'Payer le pass visite')

@section('content')
    <div class="max-w-xl mx-auto px-6 py-12">
        <h1 class="text-2xl font-semibold mb-6">Payer le pass visite</h1>
        <livewire:payment.initiate-deposit
            :amount="$visitPass->amount"
            purpose="visit_pass"
            :reference-id="$visitPass->reference"
        />
    </div>
@endsection@extends('layouts.base')

@section('title', 'Paiement du pass visite')

@section('content')
<div class="font-body bg-background dark:bg-gray-950 text-[#0F0E0C] dark:text-white antialiased min-h-screen">
    <div class="max-w-4xl mx-auto px-6 py-10 pb-20">

        {{-- Breadcrumb --}}
        <nav aria-label="Fil d'Ariane" class="flex items-center gap-2 text-xs text-[#6B6660] dark:text-gray-400 mb-10 font-body">
            <a href="{{ route('index') }}" class="hover:text-primary dark:hover:text-primary-400 transition-colors">Accueil</a>
            <i data-lucide="chevron-right" class="w-3 h-3"></i>
            <a href="{{ route('my-visit-passes.index') }}" class="hover:text-primary dark:hover:text-primary-400 transition-colors">Mes pass visite</a>
            <i data-lucide="chevron-right" class="w-3 h-3"></i>
            <span class="dark:text-gray-300">{{ $visitPass->reference }}</span>
        </nav>

        <h1 class="font-display font-semibold text-3xl mb-2">Faire le paiement</h1>
        <p class="text-sm text-gray-500 dark:text-gray-400 mb-8">Saisissez vos coordonnées Mobile Money pour confirmer le paiement.</p>

        <form action="{{ route('my-visit-passes.initiate-payment', $visitPass) }}" method="POST">
            @csrf

            <div class="space-y-4">
                <input type="text" name="phone_number" required placeholder="Numéro Mobile Money"
                    class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                <select name="provider" required
                    class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                    <option value="">Choisir l'opérateur</option>
                    @foreach(config('pawapay.providers', []) as $code => $label)
                        <option value="{{ $code }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div class="mt-6 flex flex-col sm:flex-row items-center gap-3">
                <button type="submit"
                    class="inline-flex w-full sm:w-auto items-center justify-center gap-2 rounded-xl bg-primary text-white px-6 py-3 text-sm font-semibold hover:bg-primary/90 transition-colors">
                    <i data-lucide="wallet" class="w-4 h-4"></i>
                    Payer {{ number_format($visitPass->amount, 0, ',', ' ') }} {{ $currency }}
                </button>
                <a href="{{ route('my-visit-passes.show', $visitPass) }}"
                    class="inline-flex w-full sm:w-auto items-center justify-center gap-2 rounded-xl border border-gray-200 dark:border-gray-600 px-6 py-3 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                    Annuler
                </a>
            </div>
        </form>
    </div>
</div>
@endsection