@extends('layouts.dashboard')

@section('title', 'Créer une boutique')

@section('content')
    @php
        $adminRoutePrefix = 'admin.boutique';
        $priceTypes = ['monthly' => 'Mensuel / mois', 'daily' => 'Journalier / jour', 'sale' => 'Vente'];
    @endphp
    <h1>Créer une boutique</h1>
    <x-container-dashed>
        <form action="{{ route($adminRoutePrefix . '.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
            @csrf

            <input type="hidden" name="is_active" value="1">
            <input type="hidden" name="is_verify" value="1">

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <x-form.input name="name" label="Nom de la boutique *" value="{{ old('name') }}" required />
                <x-form.select name="category_id" label="Catégorie" :options="$categories"
                    placeholder="Choisir une catégorie" value="{{ old('category_id') }}" />
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <x-form.input name="price" label="Prix (FCFA) *" type="number" step="1000" value="{{ old('price') }}" required />
                <x-form.select name="price_type" label="Type de prix *" :options="$priceTypes"
                    placeholder="Choisir le type de prix" value="{{ old('price_type') }}" required />
                <x-form.input name="surface" label="Surface (m²)" type="number" min="1" value="{{ old('surface') }}" />
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <x-form.input name="rooms" label="Pièces" type="number" min="0" value="{{ old('rooms', 0) }}" />
                <x-form.input name="phone" label="Téléphone" type="tel" value="{{ old('phone') }}" />
                <x-form.input name="email" label="Email" type="email" value="{{ old('email') }}" />
            </div>

            <x-form.textarea name="description" label="Description" rows="4">{{ old('description') }}</x-form.textarea>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <x-form.input name="address" label="Adresse" value="{{ old('address') }}" />
                <x-form.input name="location" label="Localisation / repère" value="{{ old('location') }}" />
                <x-form.select name="city_id" label="Ville *" :options="$cities"
                    placeholder="Sélectionnez une ville" value="{{ old('city_id') }}" required />
                <x-form.select name="arrondissement_id" label="Arrondissement" :options="$arrondissements"
                    placeholder="Sélectionnez un arrondissement" value="{{ old('arrondissement_id') }}" />
            </div>

            <x-form.multi-select name="amenities" label="Équipements" :options="$amenities" />

            <div>
                <x-form.file-input name="images" label="Images" accept="image/*" multiple="{{ true }}" />
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">Formats acceptés : JPG, PNG, WEBP</p>
            </div>

            <div class="flex items-center gap-3">
                <label class="inline-flex items-center gap-3 cursor-pointer">
                    <input type="checkbox" name="conditions" value="1" required
                        class="w-5 h-5 text-primary-600 border-gray-300 dark:border-gray-700 rounded focus:ring-primary-500 cursor-pointer">
                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
                        Conditions d'utilisation acceptées *
                    </span>
                </label>
            </div>

            <div class="flex gap-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                <x-btn type="submit" class="dark:bg-primary-600 dark:text-white dark:hover:bg-primary-700">
                    <x-slot:prefix>
                        <i data-lucide="check"></i>
                    </x-slot:prefix>
                    Créer la boutique
                </x-btn>
                <a href="{{ route($adminRoutePrefix . '.index') }}" class="inline-flex items-center gap-2 px-4 py-2 text-gray-600 dark:text-gray-400 hover:text-primary dark:hover:text-primary-400">
                    <i data-lucide="x"></i> Annuler
                </a>
            </div>
        </form>
    </x-container-dashed>
@endsection
