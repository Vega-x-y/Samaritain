{{-- resources/views/parcelles/edit.blade.php --}}

@extends('layouts.base')
@section('title', 'Modifier la parcelle')

@section('content')
<x-blade-components::layout.container>

    <div
        x-data="parcelleEdit({{ $id }})"
        x-init="charger()"
        class="max-w-3xl mx-auto py-8"
    >
        {{-- En-tête --}}
        <div class="flex items-center gap-3 mb-6">
            <a :href="'/parcelles/' + id" class="p-2 rounded-xl hover:bg-gray-100 transition-colors">
                <i data-lucide="chevron-left" class="w-5 h-5"></i>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Modifier la parcelle</h1>
                <p class="text-sm text-gray-500 mt-1">Modifiez les informations de la parcelle</p>
            </div>
        </div>

        {{-- Chargement --}}
        <div x-show="chargement" class="flex justify-center items-center py-16">
            <svg class="animate-spin w-8 h-8 text-emerald-500" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
            </svg>
        </div>

        {{-- Formulaire --}}
        <div x-show="!chargement" class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 space-y-6">

            {{-- Message succès --}}
            <div x-show="succes" x-transition class="bg-green-50 border border-green-200 text-green-700 rounded-xl px-4 py-3 text-sm">
                ✅ Parcelle modifiée avec succès !
            </div>

            {{-- Message erreur --}}
            <div x-show="erreur" x-transition class="bg-red-50 border border-red-200 text-red-700 rounded-xl px-4 py-3 text-sm">
                ❌ <span x-text="erreur"></span>
            </div>

            {{-- Titre --}}
            <div class="flex flex-col gap-1">
                <label class="text-sm font-semibold text-gray-700">Titre <span class="text-red-500">*</span></label>
                <input
                    type="text"
                    x-model="form.titre"
                    placeholder="Ex: Grande parcelle résidentielle"
                    class="border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500"
                />
            </div>

            {{-- Description --}}
            <div class="flex flex-col gap-1">
                <label class="text-sm font-semibold text-gray-700">Description</label>
                <textarea
                    x-model="form.description"
                    rows="3"
                    placeholder="Décrivez la parcelle..."
                    class="border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 resize-none"
                ></textarea>
            </div>

            {{-- Localisation --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="flex flex-col gap-1">
                    <label class="text-sm font-semibold text-gray-700">Ville <span class="text-red-500">*</span></label>
                    <input
                        type="text"
                        x-model="form.ville"
                        placeholder="Ex: Brazzaville"
                        class="border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500"
                    />
                </div>
                <div class="flex flex-col gap-1">
                    <label class="text-sm font-semibold text-gray-700">Quartier <span class="text-red-500">*</span></label>
                    <input
                        type="text"
                        x-model="form.quartier"
                        placeholder="Ex: Bacongo"
                        class="border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500"
                    />
                </div>
                <div class="flex flex-col gap-1">
                    <label class="text-sm font-semibold text-gray-700">Localisation <span class="text-red-500">*</span></label>
                    <input
                        type="text"
                        x-model="form.localisation"
                        placeholder="Ex: Nord de Bacongo"
                        class="border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500"
                    />
                </div>
            </div>

            {{-- Superficie & Prix --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="flex flex-col gap-1">
                    <label class="text-sm font-semibold text-gray-700">Superficie (m²) <span class="text-red-500">*</span></label>
                    <input
                        type="number"
                        x-model="form.superficie"
                        min="1"
                        class="border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500"
                    />
                </div>
                <div class="flex flex-col gap-1">
                    <label class="text-sm font-semibold text-gray-700">Prix (FCFA) <span class="text-red-500">*</span></label>
                    <input
                        type="number"
                        x-model="form.prix"
                        min="0"
                        class="border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500"
                    />
                </div>
            </div>

            {{-- Statut & Titre foncier --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="flex flex-col gap-1">
                    <label class="text-sm font-semibold text-gray-700">Statut</label>
                    <select
                        x-model="form.statut"
                        class="border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500"
                    >
                        <option value="disponible">Disponible</option>
                        <option value="vendu">Vendu</option>
                        <option value="réservé">Réservé</option>
                    </select>
                </div>
                <div class="flex flex-col gap-1">
                    <label class="text-sm font-semibold text-gray-700">Titre foncier</label>
                    <input
                        type="text"
                        x-model="form.titre_foncier"
                        placeholder="Ex: TF-12345"
                        class="border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500"
                    />
                </div>
            </div>

            {{-- Viabilisée toggle --}}
            <div class="flex items-center gap-3">
                <button
                    type="button"
                    @click="form.viabilisee = !form.viabilisee"
                    :class="form.viabilisee ? 'bg-emerald-500' : 'bg-gray-200'"
                    class="relative w-11 h-6 rounded-full transition-colors duration-200"
                >
                    <span
                        :class="form.viabilisee ? 'translate-x-5' : 'translate-x-1'"
                        class="absolute top-1 w-4 h-4 bg-white rounded-full shadow transition-transform duration-200"
                    ></span>
                </button>
                <label class="text-sm font-semibold text-gray-700">Parcelle viabilisée</label>
            </div>

            {{-- Images existantes --}}
            <div class="flex flex-col gap-2">
                <label class="text-sm font-semibold text-gray-700">Images existantes</label>
                <div x-show="imagesExistantes.length === 0" class="text-sm text-gray-400">Aucune image</div>
                <div class="grid grid-cols-3 sm:grid-cols-4 gap-3">
                    <template x-for="image in imagesExistantes" :key="image.id">
                        <div class="relative group">
                            <img :src="image.url" class="w-full h-24 object-cover rounded-xl border border-gray-200" />
                            <button
                                type="button"
                                @click="supprimerImage(image.id)"
                                class="absolute top-1 right-1 bg-red-500 text-white rounded-full w-5 h-5 text-xs flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity"
                            >✕</button>
                            <span x-show="image.principale" class="absolute bottom-1 left-1 bg-emerald-500 text-white text-xs px-1.5 py-0.5 rounded-full">
                                Principale
                            </span>
                        </div>
                    </template>
                </div>
            </div>

            {{-- Nouvelles images --}}
            <div class="flex flex-col gap-2">
                <label class="text-sm font-semibold text-gray-700">Ajouter des images</label>
                <div
                    class="border-2 border-dashed border-gray-200 rounded-xl p-6 text-center cursor-pointer hover:border-emerald-400 transition-colors"
                    @click="$refs.fileInput.click()"
                    @dragover.prevent
                    @drop.prevent="handleDrop($event)"
                >
                    <i data-lucide="upload-cloud" class="w-8 h-8 text-gray-400 mx-auto mb-2"></i>
                    <p class="text-sm text-gray-500">Cliquez ou glissez vos images ici</p>
                    <p class="text-xs text-gray-400 mt-1">PNG, JPG, WEBP — max 5MB</p>
                </div>
                <input
                    type="file"
                    x-ref="fileInput"
                    multiple
                    accept="image/*"
                    class="hidden"
                    @change="handleFiles($event)"
                />
                <div x-show="previews.length > 0" class="grid grid-cols-3 sm:grid-cols-4 gap-3 mt-2">
                    <template x-for="(preview, index) in previews" :key="index">
                        <div class="relative group">
                            <img :src="preview" class="w-full h-24 object-cover rounded-xl border border-gray-200" />
                            <button
                                type="button"
                                @click="removeImage(index)"
                                class="absolute top-1 right-1 bg-red-500 text-white rounded-full w-5 h-5 text-xs flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity"
                            >✕</button>
                        </div>
                    </template>
                </div>
            </div>

            {{-- Boutons --}}
            <div class="flex items-center gap-3 pt-2">
                <button
                    type="button"
                    @click="soumettre()"
                    :disabled="chargement"
                    class="bg-emerald-600 hover:bg-emerald-700 disabled:opacity-50 text-white font-semibold px-6 py-2.5 rounded-xl transition-colors duration-200 flex items-center gap-2"
                >
                    <svg x-show="chargement" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
                    </svg>
                    <span x-text="chargement ? 'Enregistrement...' : 'Enregistrer'"></span>
                </button>
                <a :href="'/parcelles/' + id" class="text-sm text-gray-500 hover:text-gray-700 transition-colors">
                    Annuler
                </a>
            </div>

        </div>
    </div>

</x-blade-components::layout.container>

<script>
function parcelleEdit(id) {
    return {
        id: id,
        chargement: false,
        succes: false,
        erreur: null,
        imagesExistantes: [],
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

        async charger() {
            this.chargement = true
            try {
                const res = await fetch(`/api/parcelles/${this.id}`)
                const data = await res.json()
                this.form = {
                    titre: data.titre,
                    description: data.description || '',
                    ville: data.ville,
                    quartier: data.quartier,
                    localisation: data.localisation,
                    superficie: data.superficie,
                    prix: data.prix,
                    statut: data.statut,
                    titre_foncier: data.titre_foncier || '',
                    viabilisee: data.viabilisee,
                }
                this.imagesExistantes = data.images || []
            } catch(e) {
                this.erreur = 'Erreur de chargement'
            } finally {
                this.chargement = false
            }
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

        async supprimerImage(imageId) {
            if (!confirm('Supprimer cette image ?')) return
            try {
                await fetch(`/api/parcelles/images/${imageId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    }
                })
                this.imagesExistantes = this.imagesExistantes.filter(img => img.id !== imageId)
            } catch(e) {
                this.erreur = 'Erreur suppression image'
            }
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
                const res = await fetch(`/api/parcelles/${this.id}`, {
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
                    setTimeout(() => {
                        window.location.href = `/parcelles/${this.id}`
                    }, 1500)
                }
            } catch(e) {
                this.erreur = 'Erreur de connexion au serveur'
            } finally {
                this.chargement = false
            }
        },
    }
}
</script>

@endsection