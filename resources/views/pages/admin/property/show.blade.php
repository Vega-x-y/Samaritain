@extends('layouts.dashboard')

@section('title', 'Détails du bien - ' . $property->title)

@section('content')
<div class="space-y-6">
    <!-- En-tête avec retour et actions -->
    <div class="flex justify-between items-center">
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.property.index') }}" class="text-gray-600 hover:text-gray-900">
                <i data-lucide="arrow-left" class="w-5 h-5"></i>
            </a>
            <h1>Détails du bien : {{ $property->title }}</h1>
        </div>
        <div class="flex gap-2">
            <x-btn href="{{ route('admin.property.edit', $property) }}" style="outline">
                <x-slot:prefix>
                    <i data-lucide="edit" class="w-4 h-4"></i>
                </x-slot:prefix>
                Modifier
            </x-btn>
        </div>
    </div>

    <x-container-dashed>
        <!-- Badges de statut -->
        <div class="flex gap-2 mb-6 pb-4 border-b border-gray-100">
            @if($property->is_verify)
                <span class="px-3 py-1 text-xs font-medium text-green-600 bg-green-100 rounded-full">
                    <i data-lucide="check-circle" class="inline w-3 h-3"></i> Vérifié
                </span>
            @else
                <span class="px-3 py-1 text-xs font-medium text-yellow-600 bg-yellow-100 rounded-full">
                    <i data-lucide="clock" class="inline w-3 h-3"></i> Non vérifié
                </span>
            @endif
            
            @if($property->is_active)
                <span class="px-3 py-1 text-xs font-medium text-green-600 bg-green-100 rounded-full">
                    <i data-lucide="eye" class="inline w-3 h-3"></i> Actif
                </span>
            @else
                <span class="px-3 py-1 text-xs font-medium text-red-600 bg-red-100 rounded-full">
                    <i data-lucide="eye-off" class="inline w-3 h-3"></i> Inactif
                </span>
            @endif

            @switch($property->status->value ?? 'available')
                @case('available')
                    <span class="px-3 py-1 text-xs font-medium text-green-600 bg-green-100 rounded-full">disponible</span>
                @break
                @case('sold')
                    <span class="px-3 py-1 text-xs font-medium text-red-600 bg-red-100 rounded-full">vendu</span>
                @break
                @default
                    <span class="px-3 py-1 text-xs font-medium text-blue-600 bg-blue-100 rounded-full">loué</span>
            @endswitch
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Colonne gauche - Informations principales -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Informations générales -->
                <div class="bg-sidebar rounded-lg p-4">
                    <h3 class="font-semibold text-gray-900 mb-3">Informations générales</h3>
                    <div class="grid grid-cols-2 gap-3 text-sm">
                        <div>
                            <span class="text-gray-500">Titre :</span>
                            <p class="font-medium">{{ $property->title }}</p>
                        </div>
                        <div>
                            <span class="text-gray-500">Prix :</span>
                            <p class="font-medium">{{ number_format($property->price, 0, ',', ' ') }} FCFA</p>
                        </div>
                        <div>
                            <span class="text-gray-500">Surface :</span>
                            <p class="font-medium">{{ $property->surface }} m²</p>
                        </div>
                        <div>
                            <span class="text-gray-500">Adresse :</span>
                            <p class="font-medium">{{ $property->address }}</p>
                        </div>
                        <div>
                            <span class="text-gray-500">Ville :</span>
                            <p class="font-medium">{{ $property->city->name ?? 'Non définie' }}</p>
                        </div>
                        <div>
                            <span class="text-gray-500">Arrondissement :</span>
                            <p class="font-medium">{{ $property->arrondissement->name ?? 'Non défini' }}</p>
                        </div>
                        <div>
                            <span class="text-gray-500">Catégorie :</span>
                            <p class="font-medium">{{ $property->category->name ?? 'Non définie' }}</p>
                        </div>
                        <div>
                            <span class="text-gray-500">Référence :</span>
                            <p class="font-medium">#{{ $property->id }}</p>
                        </div>
                        <div>
                            <span class="text-gray-500">Pièces :</span>
                            <p class="font-medium">{{ $property->rooms }}</p>
                        </div>
                        <div>
                            <span class="text-gray-500">Chambres :</span>
                            <p class="font-medium">{{ $property->bedrooms }}</p>
                        </div>
                        <div>
                            <span class="text-gray-500">Étages :</span>
                            <p class="font-medium">{{ $property->floor }}</p>
                        </div>
                        <div>
                            <span class="text-gray-500">Créé par :</span>
                            <p class="font-medium">{{ $property->creator->name ?? 'Admin' }}</p>
                        </div>
                    </div>
                </div>

                <!-- Description -->
                <div class="bg-sidebar rounded-lg p-4">
                    <h3 class="font-semibold text-gray-900 mb-3">Description</h3>
                    <p class="text-sm text-gray-600">{{ $property->description }}</p>
                </div>

                <!-- Équipements -->
                <div class="bg-sidebar rounded-lg p-4">
                    <h3 class="font-semibold text-gray-900 mb-3">Équipements et commodités</h3>
                    @if($property->amenities->count() > 0)
                        <div class="flex flex-wrap gap-2">
                            @foreach($property->amenities as $amenity)
                                <span class="px-3 py-1 text-xs font-medium text-blue-600 bg-blue-100 rounded-full">
                                    <i data-lucide="check" class="inline w-3 h-3"></i> {{ $amenity->name }}
                                </span>
                            @endforeach
                        </div>
                    @else
                        <p class="text-sm text-gray-500">Aucun équipement spécifié</p>
                    @endif
                </div>
            </div>

            <!-- Colonne droite - Actions et images -->
            <div class="space-y-6">
                <!-- Actions rapides -->
                <div class="bg-sidebar rounded-lg p-4">
                    <h3 class="font-semibold text-gray-900 mb-3">Actions rapides</h3>
                    <div class="space-y-3">
                        <!-- Vérification -->
                        @if(!$property->is_verify)
                            <form action="{{ route('admin.property.verify', $property) }}" method="POST">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white font-medium py-2 px-4 rounded-lg text-sm transition flex items-center justify-center gap-2">
                                    <i data-lucide="check-circle" class="w-4 h-4"></i> Vérifier ce bien
                                </button>
                            </form>
                        @else
                            <form action="{{ route('admin.property.unverify', $property) }}" method="POST">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="w-full bg-yellow-600 hover:bg-yellow-700 text-white font-medium py-2 px-4 rounded-lg text-sm transition flex items-center justify-center gap-2">
                                    <i data-lucide="x-circle" class="w-4 h-4"></i> Annuler la vérification
                                </button>
                            </form>
                        @endif

                        <!-- Activation/Désactivation -->
                        @if($property->is_active)
                            <form action="{{ route('admin.property.disable', $property) }}" method="POST">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="w-full bg-red-600 hover:bg-red-700 text-white font-medium py-2 px-4 rounded-lg text-sm transition flex items-center justify-center gap-2">
                                    <i data-lucide="ban" class="w-4 h-4"></i> Désactiver le bien
                                </button>
                            </form>
                        @else
                            <form action="{{ route('admin.property.enable', $property) }}" method="POST">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white font-medium py-2 px-4 rounded-lg text-sm transition flex items-center justify-center gap-2">
                                    <i data-lucide="play" class="w-4 h-4"></i> Activer le bien
                                </button>
                            </form>
                        @endif
                    </div>
                </div>

                <!-- Images -->
                <div class="bg-sidebar rounded-lg p-4">
                    <h3 class="font-semibold text-gray-900 mb-3">Images du bien</h3>
                    @if($property->images->count() > 0)
                        <div class="space-y-2">
                            @foreach($property->images as $image)
                                <div class="rounded-lg overflow-hidden border border-gray-200">
                                    <a href="{{ asset($image->image_url) }}" target="_blank">
                                        <img src="{{ asset($image->image_url) }}" class="w-full h-32 object-cover hover:opacity-75 transition" alt="Image">
                                    </a>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-sm text-gray-500 text-center py-4">Aucune image disponible</p>
                    @endif
                </div>

                <!-- Suppression -->
                <div class="bg-sidebar rounded-lg p-4 border border-red-200">
                    <h3 class="font-semibold text-red-600 mb-3">Zone dangereuse</h3>
                    <form action="{{ route('admin.property.destroy', $property) }}" method="POST" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce bien ? Cette action est irréversible.');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="w-full bg-red-100 hover:bg-red-200 text-red-700 font-medium py-2 px-4 rounded-lg text-sm transition flex items-center justify-center gap-2">
                            <i data-lucide="trash-2" class="w-4 h-4"></i> Supprimer définitivement
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Footer avec métadonnées -->
        <div class="mt-6 pt-4 border-t border-gray-100 text-center">
            <small class="text-gray-400 text-xs">
                ID: {{ $property->id }} | 
                Créé le: {{ $property->created_at->format('d/m/Y à H:i') }} |
                Dernière modification: {{ $property->updated_at->format('d/m/Y à H:i') }}
            </small>
        </div>
    </x-container-dashed>
</div>
@endsection