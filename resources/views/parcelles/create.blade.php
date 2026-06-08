{{-- resources/views/parcelles/create.blade.php --}}

@extends('layouts.base')
@section('title', 'Ajouter une parcelle')

@section('content')
    <x-blade-components::layout.container>

        <div class="max-w-3xl mx-auto py-8">

            {{-- En-tête --}}
            <div class="flex items-center gap-3 mb-6">
                <a href="{{ route('parcelles.store') }}" class="p-2 rounded-xl hover:bg-gray-100 transition-colors">
                    <i data-lucide="chevron-left" class="w-5 h-5"></i>
                </a>
                <div>
                    <h1 class="text-2xl font-bold text-gray-800">Ajouter une parcelle</h1>
                    <p class="text-sm text-gray-500 mt-1">Remplissez les informations de la parcelle</p>
                </div>
            </div>

            {{-- Formulaire --}}
            <div x-data="parcelleForm()" class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 space-y-6">
                {{-- Message succès --}}
                <div x-show="succes" x-transition
                    class="bg-green-50 border border-green-200 text-green-700 rounded-xl px-4 py-3 text-sm">
                    ✅ Parcelle ajoutée avec succès !
                </div>

                {{-- Message erreur --}}
                <div x-show="erreur" x-transition
                    class="bg-red-50 border border-red-200 text-red-700 rounded-xl px-4 py-3 text-sm">
                    ❌ <span x-text="erreur"></span>
                </div>

                {{-- Titre --}}
                <div class="flex flex-col gap-1">
                    <label class="text-sm font-semibold text-gray-700">Titre <span class="text-red-500">*</span></label>
                    <input type="text" x-model="form.titre" placeholder="Ex: Grande parcelle résidentielle"
                        class="border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500" />
                </div>

                {{-- Description --}}
                <div class="flex flex-col gap-1">
                    <label class="text-sm font-semibold text-gray-700">Description</label>
                    <textarea x-model="form.description" rows="3" placeholder="Décrivez la parcelle..."
                        class="border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 resize-none"></textarea>
                </div>

                {{-- Localisation --}}
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="flex flex-col gap-1">
                        <label class="text-sm font-semibold text-gray-700">Ville <span class="text-red-500">*</span></label>
                        <input type="text" x-model="form.ville" placeholder="Ex: Brazzaville"
                            class="border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500" />
                    </div>
                    <div class="flex flex-col gap-1">
                        <label class="text-sm font-semibold text-gray-700">Quartier <span
                                class="text-red-500">*</span></label>
                        <input type="text" x-model="form.quartier" placeholder="Ex: Bacongo"
                            class="border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500" />
                    </div>
                    <div class="flex flex-col gap-1">
                        <label class="text-sm font-semibold text-gray-700">Localisation <span
                                class="text-red-500">*</span></label>
                        <input type="text" x-model="form.localisation" placeholder="Ex: Nord de Bacongo"
                            class="border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500" />
                    </div>
                </div>

                {{-- Superficie & Prix --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="flex flex-col gap-1">
                        <label class="text-sm font-semibold text-gray-700">Superficie (m²) <span
                                class="text-red-500">*</span></label>
                        <input type="number" x-model="form.superficie" placeholder="Ex: 500" min="1"
                            class="border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500" />
                    </div>
                    <div class="flex flex-col gap-1">
                        <label class="text-sm font-semibold text-gray-700">Prix (FCFA) <span
                                class="text-red-500">*</span></label>
                        <input type="number" x-model="form.prix" placeholder="Ex: 5000000" min="0"
                            class="border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500" />
                    </div>
                </div>

                {{-- Statut & Viabilisée --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="flex flex-col gap-1">
                        <label class="text-sm font-semibold text-gray-700">Statut</label>
                        <select x-model="form.statut"
                            class="border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
                            <option value="disponible">Disponible</option>
                            <option value="vendu">Vendu</option>
                            <option value="réservé">Réservé</option>
                        </select>
                    </div>
                    <div class="flex flex-col gap-1">
                        <label class="text-sm font-semibold text-gray-700">Titre foncier</label>
                        <input type="text" x-model="form.titre_foncier" placeholder="Ex: TF-12345"
                            class="border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500" />
                    </div>
                </div>

                {{-- Viabilisée toggle --}}
                <div class="flex items-center gap-3">
                    <button type="button" @click="form.viabilisee = !form.viabilisee"
                        :class="form.viabilisee ? 'bg-emerald-500' : 'bg-gray-200'"
                        class="relative w-11 h-6 rounded-full transition-colors duration-200">
                        <span :class="form.viabilisee ? 'translate-x-5' : 'translate-x-1'"
                            class="absolute top-1 w-4 h-4 bg-white rounded-full shadow transition-transform duration-200"></span>
                    </button>
                    <label class="text-sm font-semibold text-gray-700">Parcelle viabilisée (eau, électricité...)</label>
                </div>

                {{-- Upload images --}}
                <div class="flex flex-col gap-2">
                    <label class="text-sm font-semibold text-gray-700">Images</label>

                    <div class="border-2 border-dashed border-gray-200 rounded-xl p-6 text-center cursor-pointer hover:border-emerald-400 transition-colors"
                        @click="$refs.fileInput.click()" @dragover.prevent @drop.prevent="handleDrop($event)">
                        <i data-lucide="upload-cloud" class="w-8 h-8 text-gray-400 mx-auto mb-2"></i>
                        <p class="text-sm text-gray-500">Cliquez ou glissez vos images ici</p>
                        <p class="text-xs text-gray-400 mt-1">PNG, JPG, WEBP — max 5MB par image</p>
                    </div>

                    <input type="file" x-ref="fileInput" multiple accept="image/*" class="hidden"
                        @change="handleFiles($event)" />

                    {{-- Aperçu images --}}
                    <div x-show="previews.length > 0" class="grid grid-cols-3 sm:grid-cols-4 gap-3 mt-2">
                        <template x-for="(preview, index) in previews" :key="index">
                            <div class="relative group">
                                <img :src="preview" class="w-full h-24 object-cover rounded-xl border border-gray-200" />
                                <button type="button" @click="removeImage(index)"
                                    class="absolute top-1 right-1 bg-red-500 text-white rounded-full w-5 h-5 text-xs flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">✕</button>
                                <span x-show="index === 0"
                                    class="absolute bottom-1 left-1 bg-emerald-500 text-white text-xs px-1.5 py-0.5 rounded-full">
                                    Principale
                                </span>
                            </div>
                        </template>
                    </div>
                </div>

                {{-- Boutons --}}
                <div class="flex items-center gap-3 pt-2">
                    <button type="button" @click="soumettre()" :disabled="chargement"
                        class="bg-emerald-600 hover:bg-emerald-700 disabled:opacity-50 text-white font-semibold px-6 py-2.5 rounded-xl transition-colors duration-200 flex items-center gap-2">
                        <svg x-show="chargement" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z" />
                        </svg>
                        <span x-text="chargement ? 'Enregistrement...' : 'Enregistrer'"></span>
                    </button>

                    <a href="{{ route('parcelles.index') }}"
                        class="text-sm text-gray-500 hover:text-gray-700 transition-colors">
                        Annuler
                    </a>
                </div>

            </div>
        </div>

    </x-blade-components::layout.container>

    <script>
        function parcelleForm() {
            return {
                chargement: false,
                succes: false,
                erreur: null,
                fichiers: [],
                previews: [],

                form: {
                    titre: '',
                    description: '',
                    ville: '',
                    quartier: '',
                    localisation: '',
                    superficie: '',
                    prix: '',
                    statut: 'disponible',
                    titre_foncier: '',
                    viabilisee: false,
                },

                handleFiles(event) {
                    const files = Array.from(event.target.files)
                    this.ajouterFichiers(files)
                },

                handleDrop(event) {
                    const files = Array.from(event.dataTransfer.files).filter(f => f.type.startsWith('image/'))
                    this.ajouterFichiers(files)
                },

                ajouterFichiers(files) {
                    files.forEach(file => {
                        this.fichiers.push(file)
                        const reader = new FileReader()
                        reader.onload = (e) => this.previews.push(e.target.result)
                        reader.readAsDataURL(file)
                    })
                },

                removeImage(index) {
                    this.fichiers.splice(index, 1)
                    this.previews.splice(index, 1)
                },

                async soumettre() {
                    this.chargement = true
                    this.succes = false
                    this.erreur = null

                    const formData = new FormData()

                    Object.entries(this.form).forEach(([key, value]) => {
                        formData.append(key, value === true ? '1' : value === false ? '0' : value)
                    })

                    this.fichiers.forEach((file, index) => {
                        formData.append(`images[${index}]`, file)
                    })

                    try {
                        const res = await fetch('/api/parcelles', {
                            method: 'POST',
                            body: formData,
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                'Accept': 'application/json',
                            }
                        })

                        const data = await res.json()

                        if (!res.ok) {
                            this.erreur = data.message || 'Une erreur est survenue'
                        } else {
                            this.succes = true
                            this.resetForm()
                            setTimeout(() => {
                                window.location.href = '/parcelles'
                            }, 1500)
                        }
                    } catch (e) {
                        this.erreur = 'Erreur de connexion au serveur'
                    } finally {
                        this.chargement = false
                    }
                },

                resetForm() {
                    this.form = {
                        titre: '',
                        description: '',
                        ville: '',
                        quartier: '',
                        localisation: '',
                        superficie: '',
                        prix: '',
                        statut: 'disponible',
                        titre_foncier: '',
                        viabilisee: false,
                    }
                    this.fichiers = []
                    this.previews = []
                }
            }
        }
    </script>

@endsection