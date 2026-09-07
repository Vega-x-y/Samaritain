@extends('layouts.base')

@section('title', 'Modifier la boutique - ' . $boutique->name)

@section('content')
    @php
        $routePrefix = 'boutique';
        $priceTypes = ['monthly' => 'Mensuel / mois', 'daily' => 'Journalier / jour', 'sale' => 'Vente'];
    @endphp

    <div class="container mx-auto px-4 py-8 max-w-5xl">
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Modifier la boutique</h1>
                <p class="text-gray-600 dark:text-gray-400 mt-1">{{ $boutique->name }}</p>
            </div>
            <a href="{{ route($routePrefix . '.show', $boutique) }}"
                class="inline-flex items-center gap-2 px-4 py-2 text-gray-600 dark:text-gray-400 hover:text-primary dark:hover:text-primary-400 transition-colors">
                <i data-lucide="chevron-left" class="w-4 h-4"></i>
                Retour
            </a>
        </div>

        <div class="bg-sidebar dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
            <form action="{{ route($routePrefix . '.update', $boutique) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                @csrf
                @method('PUT')

                <!-- Informations générales -->
                <div class="p-6 border-b border-gray-100 dark:border-gray-700">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Informations générales</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="md:col-span-2">
                            <x-form.input name="name" label="Nom de la boutique *" value="{{ old('name', $boutique->name) }}" required />
                        </div>
                        <x-form.input name="surface" label="Surface (m²)" type="number" min="1" value="{{ old('surface', $boutique->surface) }}" />
                        <x-form.input name="rooms" label="Pièces" type="number" min="0" value="{{ old('rooms', $boutique->rooms) }}" />
                        <x-form.input name="price" label="Prix (FCFA) *" type="number" step="1000" value="{{ old('price', $boutique->price) }}" required />
                        <x-form.select name="price_type" label="Type de prix *" :options="$priceTypes"
                            placeholder="Choisir le type de prix" value="{{ old('price_type', $boutique->price_type) }}" required />
                    </div>
                </div>

                <!-- Description -->
                <div class="p-6 border-b border-gray-100 dark:border-gray-700">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Description</h2>
                    <x-form.textarea name="description" label="Description de la boutique" rows="6">{{ old('description', $boutique->description) }}</x-form.textarea>
                </div>

                <!-- Localisation -->
                <div class="p-6 border-b border-gray-100 dark:border-gray-700">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Localisation</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <x-form.input name="address" label="Adresse" value="{{ old('address', $boutique->address) }}" />
                        <x-form.input name="location" label="Localisation / repère" value="{{ old('location', $boutique->location) }}" />
                        <x-form.select name="city_id" label="Ville *" :options="$cities"
                            placeholder="Sélectionnez une ville" value="{{ old('city_id', $boutique->city_id) }}" required />
                        <x-form.select name="arrondissement_id" label="Arrondissement" :options="$arrondissements"
                            placeholder="Sélectionnez un arrondissement" value="{{ old('arrondissement_id', $boutique->arrondissement_id) }}" />
                    </div>
                </div>

                <!-- Contact -->
                <div class="p-6 border-b border-gray-100 dark:border-gray-700">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Informations de contact</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <x-form.input name="phone" label="Téléphone" type="tel" value="{{ old('phone', $boutique->phone) }}" />
                        <x-form.input name="email" label="Email" type="email" value="{{ old('email', $boutique->email) }}" />
                    </div>
                </div>

                <!-- Catégorie et équipements -->
                <div class="p-6 border-b border-gray-100 dark:border-gray-700">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Catégorie & équipements</h2>
                    <div class="space-y-4">
                        <x-form.select name="category_id" label="Catégorie" :options="$categories"
                            placeholder="Choisir une catégorie" value="{{ old('category_id', $boutique->category_id) }}" />
                        <x-form.multi-select name="amenities" label="Équipements" :options="$amenities" />
                    </div>
                </div>

                @if(!$boutique->images->isEmpty())
                    <div class="p-6 border-b border-gray-100 dark:border-gray-700">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Images existantes</h2>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">Décochez les images à supprimer.</p>
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

                <!-- Nouvelles images -->
                <div class="p-6 border-b border-gray-100 dark:border-gray-700">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Ajouter des images</h2>
                    <x-form.file-input name="images" label="Images supplémentaires" accept="image/*" multiple="{{ true }}" />
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">
                        Vous pouvez ajouter plusieurs images. Formats acceptés : JPG, PNG, WEBP
                    </p>
                </div>

                <div class="p-6 flex gap-4">
                    <button type="submit" class="px-6 py-2 bg-primary text-white rounded-lg hover:bg-primary-600 dark:hover:bg-primary-700 font-medium transition flex items-center gap-2">
                        <i data-lucide="check" class="w-4 h-4"></i>
                        Enregistrer les modifications
                    </button>
                    <a href="{{ route($routePrefix . '.show', $boutique) }}" class="px-6 py-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition flex items-center gap-2">
                        <i data-lucide="x" class="w-4 h-4"></i>
                        Annuler
                    </a>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const citySelect = document.getElementById('city_id');
                const arrondissementSelect = document.getElementById('arrondissement_id');

                if (citySelect && arrondissementSelect) {
                    const originalOptions = Array.from(arrondissementSelect.querySelectorAll('option'));
                    const currentValue = arrondissementSelect.value;

                    function filterArrondissements() {
                        const cityId = citySelect.value;
                        arrondissementSelect.innerHTML = '<option value="">Sélectionnez un arrondissement</option>';

                        originalOptions.forEach(option => {
                            if (option.value === '') return;
                            const cityAttr = option.getAttribute('data-city');
                            if (!cityId || cityAttr === cityId) {
                                arrondissementSelect.appendChild(option.cloneNode(true));
                            }
                        });

                        if (currentValue && Array.from(arrondissementSelect.options).some(opt => opt.value == currentValue)) {
                            arrondissementSelect.value = currentValue;
                        }

                        arrondissementSelect.dispatchEvent(new Event('change'));
                    }

                    citySelect.addEventListener('change', filterArrondissements);
                    filterArrondissements();
                }
            });
        </script>
    @endpush
@endsection
