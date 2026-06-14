<x-app-layout>
    <!-- Hero Section -->
    <div class="bg-primary text-white overflow-hidden">
        <div class="relative max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12 md:py-16">
            <div class="flex items-center gap-3 mb-4">
                <div class="p-2 bg-white/10 rounded-xl backdrop-blur-sm">
                    <i data-lucide="edit" class="w-6 h-6"></i>
                </div>
                <div>
                    <h1 class="text-3xl md:text-4xl font-bold">Modifier la réalisation</h1>
                    <p class="text-white/90">Mettez à jour les informations de votre projet</p>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="bg-white rounded-2xl shadow-xl border border-gray-100 overflow-hidden">
            <div class="p-6 md:p-8">
                <form action="{{ route('artisan.projects.update', [$artisan, $project]) }}" method="POST"
                    enctype="multipart/form-data" class="space-y-8">
                    @csrf
                    @method('PUT')

                    <!-- Section Informations -->
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
                            <div class="p-1.5 bg-primary/35 rounded-lg">
                                <svg class="w-4 h-4 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            Détails du projet
                        </h2>
                        
                        <div class="space-y-5">
                            <x-form.input 
                                name="title" 
                                label="Titre du projet" 
                                placeholder="Ex: Rénovation complète d'un appartement"
                                :value="old('title', $project->title)" 
                                icon="folder"
                                required 
                            />

                            <x-form.textarea 
                                name="description" 
                                label="Description" 
                                placeholder="Décrivez votre réalisation, les techniques utilisées, les matériaux..."
                                rows="4"
                            >{{ old('description', $project->description) }}</x-form.textarea>

                            <!-- Image actuelle -->
                            @if ($project->image)
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Photo actuelle</label>
                                    <div class="relative inline-block w-full">
                                        <img src="{{ Storage::url($project->image) }}" alt="{{ $project->title }}"
                                            class="w-full h-48 object-cover rounded-xl border border-gray-200 shadow-sm">
                                        <div class="absolute bottom-2 right-2 bg-green-500 text-white text-xs px-2 py-1 rounded-full flex items-center gap-1">
                                            <i data-lucide="check" class="w-3 h-3"></i>
                                            Actuelle
                                        </div>
                                    </div>
                                </div>
                            @endif

                            <!-- Nouvelle image -->
                            <div>
                                <x-form.file-input 
                                    name="image" 
                                    label="Nouvelle photo" 
                                    accept="image/*"
                                    helper="Laissez vide pour conserver l'image actuelle. Max 5 Mo. Formats : JPG, PNG, WebP"
                                    icon="camera"
                                />
                            </div>
                        </div>
                    </div>

                    <!-- Conseils -->
                    <div class="bg-blue-50 border-l-4 border-primary rounded-xl p-4">
                        <div class="flex items-start gap-3">
                            <svg class="w-5 h-5 text-primary flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <div>
                                <p class="text-sm text-primary-800 font-medium mb-1">Conseils pour une belle réalisation :</p>
                                <ul class="text-sm text-primary-700 space-y-1">
                                    <li class="flex items-center gap-2">✓ Photo de bonne qualité, bien éclairée</li>
                                    <li class="flex items-center gap-2">✓ Montrez le résultat final de votre travail</li>
                                    <li class="flex items-center gap-2">✓ Une description détaillée valorise votre savoir-faire</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- Boutons d'action -->
                    <div class="flex flex-col sm:flex-row justify-end gap-3 pt-4 border-t border-gray-100">
                        <x-btn href="{{ route('artisan.projects.index', $artisan) }}" style="outline">
                            Annuler
                        </x-btn>
                        <x-btn type="submit">
                            <x-slot:prefix>
                                <i data-lucide="save"></i>
                            </x-slot:prefix>
                            Mettre à jour
                        </x-btn>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>