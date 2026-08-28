@extends('layouts.base')

@section('title', 'Payer le pass visite')

@section('content')
    <div class="font-body min-h-screen bg-background text-[#0F0E0C] dark:bg-gray-950 dark:text-white">
        <div class="mx-auto max-w-xl px-6 py-12">
            <a href="{{ route('my-visit-passes.show', $visitPass) }}"
                class="mb-6 inline-flex items-center gap-2 text-sm text-gray-500 hover:text-primary dark:text-gray-400">
                <i data-lucide="arrow-left" class="h-4 w-4"></i>
                Retour au pass
            </a>

            <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                <h1 class="text-2xl font-semibold">Payer le pass visite</h1>
                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                    Montant à payer :
                    <strong class="text-gray-900 dark:text-white">
                        {{ number_format($visitPass->amount, 0, ',', ' ') }} {{ $currency }}
                    </strong>
                </p>

                @if (session('error'))
                    <div class="mt-5 rounded-lg border border-red-200 bg-red-50 p-3 text-sm text-red-700 dark:border-red-800 dark:bg-red-900/20 dark:text-red-300">
                        {{ session('error') }}
                    </div>
                @endif

                <form action="{{ route('my-visit-passes.initiate-payment', $visitPass) }}" method="POST" class="mt-6 space-y-5">
                    @csrf

                    <div>
                        <label for="phone_number" class="block text-sm font-medium">Numéro Mobile Money</label>
                        <div class="mt-1 flex">
                            <span class="inline-flex items-center rounded-l-lg border border-r-0 border-gray-300 bg-gray-50 px-3 text-sm text-gray-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300">+242</span>
                            <input id="phone_number" name="phone_number" type="tel" inputmode="numeric"
                                value="{{ old('phone_number') }}" required minlength="9" maxlength="15"
                                placeholder="06 123 45 67"
                                class="block w-full rounded-r-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                        </div>
                        @error('phone_number')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="provider" class="block text-sm font-medium">Opérateur</label>
                        @dump($payment_config['providers'])
                        <select id="provider" name="provider" required
                            @disabled(empty($payment_config['providers']))
                            class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white disabled:cursor-not-allowed disabled:bg-gray-100">
                            <option value="">Choisir l'opérateur</option>
                            @foreach ($payment_config['providers'] as $item)
                                <option value="{{ $item['provider'] }}" @selected(old('provider') === $item['provider'])>{{ $item['displayName'] }}</option>
                            @endforeach
                        </select>
                        @error('provider')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        @if ($providerError)
                            <p class="mt-1 text-sm text-amber-600">{{ $providerError }}</p>
                        @endif
                    </div>

                    <button type="submit"
                        class="inline-flex w-full items-center justify-center gap-2 rounded-lg bg-primary px-5 py-3 text-sm font-semibold text-white transition hover:bg-primary/90">
                        <i data-lucide="wallet" class="h-4 w-4"></i>
                        Confirmer le paiement
                    </button>
                </form>
            </div>
        </div>
    </div>
@endsection
