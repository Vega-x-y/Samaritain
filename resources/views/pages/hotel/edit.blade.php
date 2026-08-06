@extends('layouts.base')

@section('title', 'Modifier l\'hôtel')

@section('content')
    <div class="container mx-auto px-4 sm:px-6 lg:px-8 py-6 md:py-8 max-w-4xl">
        <nav class="flex items-center gap-2 text-sm text-muted-foreground dark:text-gray-400 mb-6">
            <a href="{{ route('index') }}" class="hover:text-primary dark:hover:text-primary-400 transition-colors">Accueil</a>
            <i data-lucide="chevron-right" class="w-3 h-3"></i>
            <a href="{{ route('hotel.index') }}" class="hover:text-primary dark:hover:text-primary-400 transition-colors">Hôtels</a>
            <i data-lucide="chevron-right" class="w-3 h-3"></i>
            <span class="text-foreground dark:text-gray-300">Modifier l'hôtel</span>
        </nav>

        <h1 class="text-2xl md:text-3xl font-bold text-foreground dark:text-white mb-6">Modifier l'hôtel</h1>

        <form action="{{ route('hotel.update', $hotel) }}" method="POST" enctype="multipart/form-data"
            class="bg-card dark:bg-gray-800 rounded-2xl border border-border dark:border-gray-700 p-6 space-y-6">
            @csrf
            @method('PUT')

            <!-- Informations de base -->
            <div>
                <h2 class="text-lg font-semibold text-foreground dark:text-white mb-4">Informations de base</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-foreground dark:text-gray-300 mb-1">Titre *</label>
                        <input type="text" name="title" value="{{ old('title', $hotel->title) }}" required minlength="8"
                            class="w-full px-4 py-2.5 border border-border dark:border-gray-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-ring dark:focus:ring-primary/30 bg-background dark:bg-gray-900 text-foreground dark:text-white @error('title') border-red-500 @enderror">
                        @error('title') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-foreground dark:text-gray-300 mb-1">Description</label>
                        <textarea name="description" rows="4"
                            class="w-full px-4 py-2.5 border border-border dark:border-gray-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-ring dark:focus:ring-primary/30 bg-background dark:bg-gray-900 text-foreground dark:text-white @error('description') border-red-500 @enderror">{{ old('description', $hotel->description) }}</textarea>
                        @error('description') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-foreground dark:text-gray-300 mb-1">Prix / nuit (FCFA) *</label>
                        <input type="number" name="price_per_night" value="{{ old('price_per_night', $hotel->price_per_night) }}" required min="0"
                            class="w-full px-4 py-2.5 border border-border dark:border-gray-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-ring dark:focus:ring-primary/30 bg-background dark:bg-gray-900 text-foreground dark:text-white @error('price_per_night') border-red-500 @enderror">
                        @error('price_per_night') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-foreground dark:text-gray-300 mb-1">Prix / heure par défaut (FCFA) *</label>
                        <input type="number" name="price_per_hour" value="{{ old('price_per_hour', $hotel->price_per_hour) }}" required min="0"
                            class="w-full px-4 py-2.5 border border-border dark:border-gray-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-ring dark:focus:ring-primary/30 bg-background dark:bg-gray-900 text-foreground dark:text-white @error('price_per_hour') border-red-500 @enderror">
                        @error('price_per_hour') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        <p class="text-xs text-muted-foreground dark:text-gray-400 mt-1">Prix utilisé si aucun prix spécifique n'est défini</p>
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-foreground dark:text-gray-300 mb-1">Numéro de contact</label>
                        <input type="text" name="contact" value="{{ old('contact', $hotel->contact) }}" placeholder="+221 77 123 45 67"
                            class="w-full px-4 py-2.5 border border-border dark:border-gray-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-ring dark:focus:ring-primary/30 bg-background dark:bg-gray-900 text-foreground dark:text-white @error('contact') border-red-500 @enderror">
                        @error('contact') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-foreground dark:text-gray-300 mb-1">Étoiles *</label>
                        <select name="star_rating" required
                            class="w-full px-4 py-2.5 border border-border dark:border-gray-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-ring dark:focus:ring-primary/30 bg-background dark:bg-gray-900 text-foreground dark:text-white">
                            @for($i = 1; $i <= 5; $i++)
                                <option value="{{ $i }}" {{ old('star_rating', $hotel->star_rating) == $i ? 'selected' : '' }}>{{ $i }} étoile(s)</option>
                            @endfor
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-foreground dark:text-gray-300 mb-1">Chambres *</label>
                        <input type="number" name="rooms" value="{{ old('rooms', $hotel->rooms) }}" required min="1"
                            class="w-full px-4 py-2.5 border border-border dark:border-gray-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-ring dark:focus:ring-primary/30 bg-background dark:bg-gray-900 text-foreground dark:text-white">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-foreground dark:text-gray-300 mb-1">Salles de bain *</label>
                        <input type="number" name="bathrooms" value="{{ old('bathrooms', $hotel->bathrooms) }}" required min="0"
                            class="w-full px-4 py-2.5 border border-border dark:border-gray-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-ring dark:focus:ring-primary/30 bg-background dark:bg-gray-900 text-foreground dark:text-white">
                    </div>

                    <div>
                        <label class="flex items-center gap-2 text-sm font-medium text-foreground dark:text-gray-300 mt-6">
                            <input type="checkbox" name="furnished" value="1" {{ old('furnished', $hotel->furnished) ? 'checked' : '' }}
                                class="h-4 w-4 rounded border-gray-300 dark:border-gray-600 text-primary focus:ring-primary">
                            Meublé
                        </label>
                    </div>
                </div>
            </div>

            <!-- Prix horaires détaillés (optionnel) -->
            <div x-data="{ showHourlyPrices: false }">
                <button type="button" @click="showHourlyPrices = !showHourlyPrices"
                    class="flex items-center justify-between w-full text-left mb-4">
                    <div>
                        <h2 class="text-lg font-semibold text-foreground dark:text-white">Prix par heure <span class="text-sm font-normal text-muted-foreground">(optionnel)</span></h2>
                        <p class="text-sm text-muted-foreground dark:text-gray-400 mt-1">Définissez des prix spécifiques pour certaines heures. Laissez vide pour utiliser le prix par défaut.</p>
                    </div>
                    <i data-lucide="chevron-down" class="w-5 h-5 text-muted-foreground transition-transform duration-200" :class="showHourlyPrices ? 'rotate-180' : ''"></i>
                </button>

                <div x-show="showHourlyPrices" x-collapse class="grid grid-cols-2 md:grid-cols-4 gap-3">
                    @for($hour = 1; $hour <= 24; $hour++)
                        <div>
                            <label class="block text-xs font-medium text-foreground dark:text-gray-300 mb-1">{{ $hour }}h</label>
                            <input type="number" name="hourly_prices[{{ $hour }}]" value="{{ old('hourly_prices.' . $hour, $hotel->hourly_prices[$hour] ?? null) }}" min="0" placeholder="Prix"
                                class="w-full px-3 py-2 text-sm border border-border dark:border-gray-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-ring dark:focus:ring-primary/30 bg-background dark:bg-gray-900 text-foreground dark:text-white @error('hourly_prices.' . $hour) border-red-500 @enderror">
                        </div>
                    @endfor
                </div>
                @error('hourly_prices') <p class="text-xs text-red-600 mt-2">{{ $message }}</p> @enderror
            </div>

            <!-- Localisation -->
            <div>
                <h2 class="text-lg font-semibold text-foreground dark:text-white mb-4">Localisation</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-foreground dark:text-gray-300 mb-1">Adresse *</label>
                        <input type="text" name="address" value="{{ old('address', $hotel->address) }}" required minlength="8"
                            class="w-full px-4 py-2.5 border border-border dark:border-gray-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-ring dark:focus:ring-primary/30 bg-background dark:bg-gray-900 text-foreground dark:text-white @error('address') border-red-500 @enderror">
                        @error('address') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-foreground dark:text-gray-300 mb-1">Ville *</label>
                        <select name="city_id" required
                            class="w-full px-4 py-2.5 border border-border dark:border-gray-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-ring dark:focus:ring-primary/30 bg-background dark:bg-gray-900 text-foreground dark:text-white">
                            <option value="">Sélectionnez une ville</option>
                            @foreach($cities as $city)
                                <option value="{{ $city->id }}" {{ old('city_id', $hotel->city_id) == $city->id ? 'selected' : '' }}>{{ $city->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-foreground dark:text-gray-300 mb-1">Arrondissement</label>
                        <select name="arrondissement_id"
                            class="w-full px-4 py-2.5 border border-border dark:border-gray-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-ring dark:focus:ring-primary/30 bg-background dark:bg-gray-900 text-foreground dark:text-white">
                            <option value="">Sélectionnez un arrondissement</option>
                            @foreach($arrondissements as $arrondissement)
                                <option value="{{ $arrondissement->id }}" {{ old('arrondissement_id', $hotel->arrondissement_id) == $arrondissement->id ? 'selected' : '' }}>{{ $arrondissement->name }}</option>
                            @endforeach
                        </select>
                    </div>

                </div>
            </div>

            <!-- Équipements -->
            <div>
                <h2 class="text-lg font-semibold text-foreground dark:text-white mb-4">Équipements</h2>
                <div class="flex flex-wrap gap-3">
                    @php $selectedAmenities = $hotel->amenities->pluck('id')->toArray(); @endphp
                    @foreach($amenities as $amenity)
                        <label class="inline-flex items-center gap-2 px-3 py-2 border border-border dark:border-gray-700 rounded-lg cursor-pointer hover:bg-primary/5 dark:hover:bg-primary/10 transition">
                            <input type="checkbox" name="amenities[]" value="{{ $amenity->id }}"
                                {{ in_array($amenity->id, $selectedAmenities) ? 'checked' : '' }}
                                class="h-4 w-4 rounded border-gray-300 dark:border-gray-600 text-primary focus:ring-primary">
                            <span class="text-sm text-foreground dark:text-gray-300">{{ $amenity->name }}</span>
                        </label>
                    @endforeach
                </div>
            </div>

            <!-- Images existantes -->
            @if($hotel->images->isNotEmpty())
                <div>
                    <h2 class="text-lg font-semibold text-foreground dark:text-white mb-4">Photos actuelles</h2>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        @foreach($hotel->images as $image)
                            <div class="relative group">
                                <img src="{{ $image->image_url }}" alt="{{ $hotel->title }}"
                                    class="w-full h-32 object-cover rounded-lg">
                                <label class="absolute top-2 right-2 bg-white/90 dark:bg-gray-800/90 rounded-full p-1.5 cursor-pointer shadow">
                                    <input type="checkbox" name="kept_images[]" value="{{ $image->id }}" checked
                                        class="sr-only">
                                    <i data-lucide="trash-2" class="w-4 h-4 text-red-500"></i>
                                </label>
                            </div>
                        @endforeach
                    </div>
                    <p class="text-xs text-muted-foreground dark:text-gray-400 mt-2">Décochez une photo pour la supprimer.</p>
                </div>
            @endif

            <!-- Nouvelles images -->
            <div>
                <h2 class="text-lg font-semibold text-foreground dark:text-white mb-4">Ajouter des photos</h2>
                <input type="file" name="images[]" multiple accept="image/*"
                    class="w-full px-4 py-2.5 border border-border dark:border-gray-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-ring dark:focus:ring-primary/30 bg-background dark:bg-gray-900 text-foreground dark:text-white">
                <p class="text-xs text-muted-foreground dark:text-gray-400 mt-1">JPG, PNG, WEBP (max 10MB par image)</p>
            </div>

            <div class="flex gap-3 pt-4 border-t border-border dark:border-gray-700">
                <button type="submit"
                    class="px-6 py-2.5 bg-primary dark:bg-primary-600 text-white rounded-lg font-semibold hover:bg-primary/90 dark:hover:bg-primary-700 transition">
                    Enregistrer les modifications
                </button>
                <a href="{{ route('hotel.dashboard') }}"
                    class="px-6 py-2.5 border border-border dark:border-gray-700 rounded-lg text-muted-foreground dark:text-gray-400 hover:bg-muted dark:hover:bg-gray-700 transition">
                    Annuler
                </a>
            </div>
        </form>
    </div>
@endsection