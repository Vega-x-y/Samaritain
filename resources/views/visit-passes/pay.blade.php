@extends('layouts.base')

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
        <p class="text-sm text-gray-500 dark:text-gray-400 mb-8">Choisissez votre opérateur Mobile Money et renseignez votre numéro. Vous validerez le paiement depuis votre téléphone.</p>

        @if ($errors->any())
            <div class="mb-6 p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-xl text-red-700 dark:text-red-400 text-sm">
                <div class="flex items-start gap-2">
                    <i data-lucide="alert-circle" class="w-4 h-4 mt-0.5 shrink-0"></i>
                    <ul class="space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif

        <form action="{{ route('my-visit-passes.initiate-payment', $visitPass) }}" method="POST">
            @csrf

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
                @foreach ($providers as $code => $label)
                    <label class="relative block cursor-pointer rounded-xl border-2 p-5 transition has-[:checked]:border-primary has-[:checked]:bg-primary/5 border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 hover:border-gray-300 dark:hover:border-gray-600">
                        <input type="radio" name="provider" value="{{ $code }}"
                            class="peer sr-only" required
                            @checked(old('provider') === $code)>
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-lg bg-primary/10 text-primary flex items-center justify-center">
                                    <i data-lucide="smartphone" class="w-5 h-5"></i>
                                </div>
                                <div>
                                    <p class="font-semibold text-sm dark:text-white">{{ $label }}</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ $code }}</p>
                                </div>
                            </div>
                            <span class="h-5 w-5 rounded-full border-2 border-gray-300 dark:border-gray-600 peer-checked:border-primary peer-checked:bg-primary [&amp;:has(input:checked)]:bg-primary"></span>
                        </div>
                    </label>
                @endforeach
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-sm border border-gray-100 dark:border-gray-700">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2" for="phone">
                    Numéro Mobile Money <span class="text-xs text-gray-400">(avec ou sans +, ex. +24206000000)</span>
                </label>
                <input type="tel" id="phone" name="phone" value="{{ old('phone') }}" required inputmode="tel"
                    placeholder="+242 06 000 00 00"
                    class="w-full rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-primary" />
            </div>

            <div class="mt-6 flex flex-col sm:flex-row items-center gap-3">
                <button type="submit"
                    class="inline-flex w-full sm:w-auto items-center justify-center gap-2 rounded-xl bg-primary text-white px-6 py-3 text-sm font-semibold hover:bg-primary/90 transition-colors">
                    <i data-lucide="wallet" class="w-4 h-4"></i>
                    Valider le paiement ({{ number_format($visitPass->amount, 0, ',', ' ') }} {{ $currency }})
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