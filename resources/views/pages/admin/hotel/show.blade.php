@extends('layouts.dashboard')

@section('title', 'Détails de l\'hôtel')

@section('content')
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800 dark:text-white">{{ $hotel->title }}</h1>
        <a href="{{ route('admin.hotel.index') }}" class="px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600">
            Retour à la liste
        </a>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
        <h2 class="text-xl font-semibold mb-4 text-gray-800 dark:text-white">Informations de l'hôtel</h2>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <span class="text-gray-600 dark:text-gray-400">Titre:</span>
                <p class="text-gray-800 dark:text-white font-medium">{{ $hotel->title }}</p>
            </div>
            
            <div>
                <span class="text-gray-600 dark:text-gray-400">Prix par nuit:</span>
                <p class="text-gray-800 dark:text-white font-medium">{{ number_format($hotel->price_per_night, 0, ',', ' ') }} FCFA</p>
            </div>
            
            <div>
                <span class="text-gray-600 dark:text-gray-400">Étoiles:</span>
                <p class="text-gray-800 dark:text-white">{{ $hotel->star_rating }} / 5</p>
            </div>
            
            <div>
                <span class="text-gray-600 dark:text-gray-400">Adresse:</span>
                <p class="text-gray-800 dark:text-white">{{ $hotel->address }}</p>
            </div>
            
            <div>
                <span class="text-gray-600 dark:text-gray-400">Ville:</span>
                <p class="text-gray-800 dark:text-white">{{ $hotel->city->name ?? 'N/A' }}</p>
            </div>
            
            <div>
                <span class="text-gray-600 dark:text-gray-400">Statut de vérification:</span>
                @if($hotel->is_verify)
                    <span class="px-3 py-1 text-sm font-medium text-green-500 bg-green-300 dark:bg-green-900/30 dark:text-green-400 rounded-full">Vérifié</span>
                @else
                    <span class="px-3 py-1 text-sm font-medium text-yellow-500 bg-yellow-300 dark:bg-yellow-900/30 dark:text-yellow-400 rounded-full">Non vérifié</span>
                @endif
            </div>
            
            <div>
                <span class="text-gray-600 dark:text-gray-400">Statut d'activation:</span>
                @if($hotel->is_active)
                    <span class="px-3 py-1 text-sm font-medium text-green-500 bg-green-300 dark:bg-green-900/30 dark:text-green-400 rounded-full">Actif</span>
                @else
                    <span class="px-3 py-1 text-sm font-medium text-red-500 bg-red-300 dark:bg-red-900/30 dark:text-red-400 rounded-full">Inactif</span>
                @endif
            </div>
        </div>

        <div class="mt-6 flex gap-2">
            @if(!$hotel->is_verify)
                <form action="{{ route('admin.hotel.verify', $hotel) }}" method="POST">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="px-4 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600">
                        Vérifier
                    </button>
                </form>
            @else
                <form action="{{ route('admin.hotel.unverify', $hotel) }}" method="POST">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="px-4 py-2 bg-yellow-500 text-white rounded-lg hover:bg-yellow-600">
                        Annuler vérification
                    </button>
                </form>
            @endif
            
            @if($hotel->is_active)
                <form action="{{ route('admin.hotel.disable', $hotel) }}" method="POST">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600">
                        Désactiver
                    </button>
                </form>
            @else
                <form action="{{ route('admin.hotel.enable', $hotel) }}" method="POST">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="px-4 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600">
                        Activer
                    </button>
                </form>
            @endif
        </div>
    </div>
@endsection
</parameter>
{{-- <task_progress>
- [x] Examiner la structure admin pour les biens
- [x] Examiner le layout dashboard admin
- [x] Créer un AdminHotelController
- [x] Ajouter les routes admin pour les hôtels
- [x] Créer la vue de validation des hôtels
- [x] Ajouter l'onglet Hotel dans le dashboard admin
- [x] Créer un test pour vérifier le fonctionnement
- [x] Convertir le test en syntaxe Pest
- [x] Examiner HotelTest.php pour comprendre la structure
- [x] Corriger les tests AdminHotelTest
- [x] Créer le rôle staff dans les tests
- [x] Définir is_staff dans les tests
- [x] Vérifier le AdminHotelController
- [x] Régénérer l'autoloader Composer
- [x] Ajouter l'import du contrôleur dans les routes
- [x] Créer la vue show.blade.php
- [ ] Exécuter les tests
</task_progress> --}}
</write_to_file>