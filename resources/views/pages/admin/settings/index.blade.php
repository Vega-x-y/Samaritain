@extends('layouts.dashboard')

@section('title', 'Paramètres')

@section('content')
    <div class="mb-8">
        <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Paramètres Généraux</h1>
        <p class="text-gray-500 dark:text-gray-400 mt-1">Configurez les paramètres de fonctionnement de la plateforme.</p>
    </div>

    @if(session('success'))
        <div class="bg-emerald-50 dark:bg-emerald-900/30 border border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-400 p-4 rounded-lg mb-6 flex items-center">
            <i data-lucide="check-circle" class="w-5 h-5 mr-3"></i>
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
        <div class="p-6">
            <h2 class="text-lg font-semibold text-gray-800 dark:text-white mb-4">Finances & Commissions</h2>
            
            <form action="{{ route('admin.settings.update') }}" method="POST">
                @csrf
                @method('PUT')
                
                <div class="mb-6 max-w-md">
                    <label for="artisan_commission_percent" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Commission sur les acomptes artisan (%)
                    </label>
                    <div class="relative">
                        <input type="number" 
                               id="artisan_commission_percent" 
                               name="artisan_commission_percent" 
                               value="{{ old('artisan_commission_percent', $commissionPercent) }}" 
                               min="0" max="100" 
                               class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500 pr-8">
                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                            <span class="text-gray-500 sm:text-sm">%</span>
                        </div>
                    </div>
                    @error('artisan_commission_percent')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                    <p class="text-gray-500 text-xs mt-2">
                        Ce pourcentage sera déduit de l'acompte payé par le client via PawaPay.
                        Le reste sera crédité sur le portefeuille (wallet) de l'artisan.
                    </p>
                </div>

                <div class="flex justify-start">
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-6 rounded-lg transition shadow-sm">
                        Enregistrer les modifications
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
