@extends('layouts.dashboard')

@section('content')
    @php
        $status = [
            'available' => 'Disponible',
            'sold' => 'Vendu',
            'rented' => 'Loué',
        ];
    @endphp
    <h1>Créer un bien</h1>
    <x-container-dashed>
        <form action="{{ route('admin.property.store') }}" method="POST" class="space-y-4" enctype="multipart/form-data">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-5 gap-6">
                <div class="md:col-span-2">
                    <x-form.input name="title" label="Titre du bien" />
                </div>
                <x-form.input name="surface" label="Surface (m²)" type="number" step="0.01" />
                <x-form.input name="price" label="Prix" type="number" step="0.01" />
                <x-form.select name="category_id" label="Catégorie" placeholder="Choisir une catégorie" :options="[
                    '1' => 'Appartement',
                    '2' => 'Maison',
                    '3' => 'Studio',
                    '4' => 'Local commercial',
                ]" />
            </div>

            <x-form.textarea name="description" label="Description" />

            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <x-form.input name="rooms" label="Pièces" type="number" />
                <x-form.input name="bedrooms" label="Chambres" type="number" />
                <x-form.input name="bathrooms" label="Salle de bains" type="number" />
                <x-form.input name="floor" label="Étages" type="number" />
            </div>

            <div>
                <x-form.multi-select name="equipments" label="Équipements" :options="[
                    'wifi' => 'Wi-Fi',
                    'pool' => 'Piscine',
                    'garage' => 'Garage',
                    'garden' => 'Jardin',
                    'security' => 'Sécurité',
                ]" />
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <x-form.input name="address" label="Adresse" />
                <x-form.select name="city_id" label="Ville" placeholder="Choisir une ville" :options="[
                    '1' => 'Brazzaville',
                    '2' => 'Pointe-Noire',
                ]" />
                <x-form.select name="status" label="Statut" placeholder="Choisir un statut" :options="[
                    'available' => 'Disponible',
                    'sold' => 'Vendu',
                    'rented' => 'Loué',
                ]" />
            </div>

            <div>
                <x-form.file-input name="image" label="Images" accept="image/*" />
            </div>

            <div class="flex items-center gap-3">
                <x-btn type="submit">
                    Ajouter le bien
                </x-btn>
                <x-btn href="{{ route('admin.property.index') }}" style="outline">
                    Annuler
                </x-btn>
            </div>
        </form>
    </x-container-dashed>
@endsection
