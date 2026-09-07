@extends('layouts.dashboard')

@section('title', 'Modifier la boutique - ' . $boutique->name)

@section('content')
    @php
        $adminRoutePrefix = 'admin.boutique';
        $priceTypes = ['monthly' => 'Mensuel / mois', 'daily' => 'Journalier / jour', 'sale' => 'Vente'];
    @endphp
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-gray-800 dark:text-white">Modifier la boutique</h1>
        <a href="{{ route($adminRoutePrefix . '.index') }}" class="text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white">
            <i data-lucide="arrow-left" class="w-5 h-5"></i>
        </a>
    </div>

    <x-container-dashed>
        <form action="{{ route($adminRoutePrefix . '.update', $boutique) }}" method="POST" enctype="multipart/form-data" class="space-y-4">
            @csrf
            @method('PUT')

            <input type="hidden" name="is_active" value="{{ $boutique->is_active ? 1 : 0 }}">
            <input type="hidden" name="is_verify" value="{{ $boutique->is_verify ? 1 : 0 }}">

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <x-form.input name="name" label="Nom de la boutique *" value="{{ old('name', $boutique->name) }}" required />
                <x-form.select name="category_id" label="Catégorie" :options="$categories"
                    placeholder="Choisir une catégorie" value="{{ old('category_id', $boutique->category_id) }}" />
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <x-form.input name="price" label="Prix (FCFA) *" type="number" step="1000" value="{{ old('price', $boutique->price) }}" required />
                <x-form.select name="price_type" label="Type de prix *" :options="$priceTypes"
                    placeholder="Choisir le type de prix" value="{{ old('price_type', $boutique->price_type) }}" required />
                <x-form.input name="surface" label="Surface (m²)" type="number" min="1" value="{{ old('surface', $boutique->surface) }}" />
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <x-form.input name="rooms" label="Pièces" type="number" min="0" value="{{ old('rooms', $boutique->rooms) }}" />
                <x-form.input name="phone" label="Téléphone" type="tel" value="{{ old('phone', $boutique->phone) }}" />
                <x-form.input name="email" label="Email" type="email" value="{{ old('email', $boutique->email) }}" />
            </div>

            <x-form.textarea name="description" label="Description" rows="4">{{ old('description', $boutique->description) }}</x-form.textarea>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <x-form.input name="address" label="Adresse" value="{{ old('address', $boutique->address) }}" />
                <x-form.input name="location" label="Localisation / repère" value="{{ old('location', $boutique->location) }}" />
                <x-form.select name="city_id" label="Ville *" :options="$cities"
                    placeholder="Sélectionnez une ville" value="{{ old('city_id', $boutique->city_id) }}" required />
                <x-form.select name="arrondissement_id" label="Arrondissement" :options="$arrondissements"
                    placeholder="Sélectionnez un arrondissement" value="{{ old('arrondissement_id', $boutique->arrondissement_id) }}" />
            </div>

            <x-form.multi-select name="amenities" label="Équipements" :options="$amenities" />

            @if(!$boutique->images->isEmpty())
                <div class="p-4 border border-gray-200 dark:border-gray-700 rounded-lg">
                    <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Images existantes</h3>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        @foreach ($boutique->images as $image)
                            <div class="relative">
                                <img src="{{ $image->image_url }}" alt="Image" class="w-full h-24 object-cover rounded-lg" />
                                <label class="inline-flex items-center gap-1 mt-1 text-xs">
                                    <input type="checkbox" name="kept_images[]" value="{{ $image->id }}" checked class="accent-primary">
                                    Conserver
                                </label>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <div>
                <x-form.file-input name="images" label="Images supplémentaires" accept="image/*" multiple="{{ true }}" />
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">Formats acceptés : JPG, PNG, WEBP</p>
            </div>

            <div class="flex gap-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                <x-btn type="submit" class="dark:bg-primary-600 dark:text-white dark:hover:bg-primary-700">
                    <x-slot:prefix>
                        <i data-lucide="check"></i>
                    </x-slot:prefix>
                    Enregistrer les modifications
                </x-btn>
                <a href="{{ route($adminRoutePrefix . '.show', $boutique) }}" class="inline-flex items-center gap-2 px-4 py-2 text-gray-600 dark:text-gray-400 hover:text-primary dark:hover:text-primary-400">
                    <i data-lucide="x"></i> Annuler
                </a>
            </div>
        </form>
    </x-container-dashed>
@endsection
