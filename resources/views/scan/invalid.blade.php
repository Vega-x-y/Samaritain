@extends('layouts.dashboard')

@section('title', 'Pass invalide')

@section('content')
    <div class="max-w-2xl mx-auto">
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="p-6 text-center">
                <div class="mb-4">
                    <div class="w-20 h-20 bg-red-100 dark:bg-red-900/30 rounded-full flex items-center justify-center mx-auto">
                        <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-red-600 dark:text-red-400">
                            <circle cx="12" cy="12" r="10"/>
                            <line x1="18" y1="6" x2="6" y2="18"/>
                            <line x1="6" y1="6" x2="18" y2="18"/>
                        </svg>
                    </div>
                </div>
                
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">Pass invalide</h1>
                <p class="text-gray-600 dark:text-gray-400 mb-6">{{ $result['message'] ?? 'Ce pass n\'est pas valide' }}</p>
                
                <div class="bg-gray-50 dark:bg-gray-900/50 rounded-lg p-4 mb-6 text-left">
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">
                        <strong>Raison :</strong> 
                        @switch($result['reason'] ?? 'unknown')
                            @case('not_found')
                                Aucun pass trouvé avec cet UUID
                                @break
                            @case('suspended')
                                Ce pass a été suspendu par un administrateur
                                @break
                            @case('expired')
                                La date de validité de ce pass est dépassée
                                @break
                            @case('used')
                                Toutes les visites de ce pass ont été utilisées
                                @break
                            @default
                                {{ $result['message'] ?? 'Pass non valide' }}
                        @endswitch
                    </p>
                    @if(isset($result['pass']))
                        <p class="text-sm text-gray-600 dark:text-gray-400">
                            <strong>Titulaire :</strong> {{ $result['pass']->holder_name }}
                        </p>
                        @if($result['pass']->expiration_date)
                        <p class="text-sm text-gray-600 dark:text-gray-400">
                            <strong>Date d'expiration :</strong> {{ $result['pass']->expiration_date->format('d/m/Y') }}
                        </p>
                        @endif
                    @endif
                </div>
                
                <div class="flex justify-center gap-3">
                    <a href="{{ route('scan.index') }}" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition">
                        Nouveau scan
                    </a>
                    <a href="{{ route('passes.index') }}" class="px-4 py-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                        Retour à l'accueil
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection