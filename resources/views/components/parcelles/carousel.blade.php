{{-- resources/views/components/parcelles/carousel.blade.php --}}

<div
    x-data="parcellesCarousel()"
    x-init="chargerParcelles()"
    class="w-full"
>
    {{-- FILTRES --}}
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-4 mb-6">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-3">

            {{-- Ville --}}
            <div class="flex flex-col gap-1">
                <label class="text-xs font-semibold text-gray-500 dark:text-gray-400">Ville</label>
                <input
                    type="text"
                    x-model="filtres.ville"
                    @input.debounce.400ms="chargerParcelles()"
                    placeholder="Ex: Brazzaville"
                    class="border border-gray-200 dark:border-gray-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-gray-700 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-emerald-500"
                />
            </div>

            {{-- Quartier --}}
            <div class="flex flex-col gap-1">
                <label class="text-xs font-semibold text-gray-500 dark:text-gray-400">Quartier</label>
                <select
                    x-model="filtres.viabilisee"
                    @change="chargerParcelles()"
                    class="border border-gray-200 dark:border-gray-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-gray-700 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-emerald-500"
                >
                    <option value="">Tous</option>
                    <option value="1">Makélékélé</option>
                    <option value="0">Bacongo</option>
                </select>
            </div>

            {{-- Statut --}}
            <div class="flex flex-col gap-1">
                <label class="text-xs font-semibold text-gray-500 dark:text-gray-400">Statut</label>
                <select
                    x-model="filtres.statut"
                    @change="chargerParcelles()"
                    class="border border-gray-200 dark:border-gray-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-gray-700 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-emerald-500"
                >
                    <option value="">Tous</option>
                    <option value="disponible">Disponible</option>
                    <option value="vendu">Vendu</option>
                    <option value="réservé">Réservé</option>
                </select>
            </div>

            {{-- Viabilisée --}}
            {{-- <div class="flex flex-col gap-1">
                <label class="text-xs font-semibold text-gray-500 dark:text-gray-400">Viabilisée</label>
                <select
                    x-model="filtres.viabilisee"
                    @change="chargerParcelles()"
                    class="border border-gray-200 dark:border-gray-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-gray-700 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-emerald-500"
                >
                    <option value="">Toutes</option>
                    <option value="1">Oui</option>
                    <option value="0">Non</option>
                </select>
            </div> --}}

            {{-- Prix min --}}
            <div class="flex flex-col gap-1">
                <label class="text-xs font-semibold text-gray-500 dark:text-gray-400">Prix min (FCFA)</label>
                <input
                    type="number"
                    x-model="filtres.prix_min"
                    @input.debounce.400ms="chargerParcelles()"
                    placeholder="0"
                    class="border border-gray-200 dark:border-gray-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-gray-700 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-emerald-500"
                />
            </div>

            {{-- Prix max --}}
            <div class="flex flex-col gap-1">
                <label class="text-xs font-semibold text-gray-500 dark:text-gray-400">Prix max (FCFA)</label>
                <input
                    type="number"
                    x-model="filtres.prix_max"
                    @input.debounce.400ms="chargerParcelles()"
                    placeholder="100 000 000"
                    class="border border-gray-200 dark:border-gray-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-gray-700 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-emerald-500"
                />
            </div>

            {{-- Superficie min --}}
            <div class="flex flex-col gap-1">
                <label class="text-xs font-semibold text-gray-500 dark:text-gray-400">Superficie min (m²)</label>
                <input
                    type="number"
                    x-model="filtres.superficie_min"
                    @input.debounce.400ms="chargerParcelles()"
                    placeholder="0"
                    class="border border-gray-200 dark:border-gray-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-gray-700 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-emerald-500"
                />
            </div>

            {{-- Bouton reset --}}
            <div class="flex flex-col gap-1 justify-end">
                <button
                    @click="resetFiltres()"
                    class="border border-gray-200 dark:border-gray-600 rounded-lg px-3 py-2 text-sm text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors duration-200"
                >
                    Réinitialiser
                </button>
            </div>

        </div>
    </div>

    {{-- ÉTAT CHARGEMENT --}}
    <div x-show="chargement" class="flex justify-center items-center py-16">
        <svg class="animate-spin w-8 h-8 text-emerald-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
        </svg>
    </div>

    {{-- ÉTAT VIDE --}}
    <div x-show="!chargement && parcelles.length === 0" class="flex flex-col items-center justify-center py-16 text-gray-400">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-16 h-16 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 20l-5.447-2.724A2 2 0 013 15.382V5.618a2 2 0 011.553-1.949l5-1.25A2 2 0 0110 2.5h4a2 2 0 01.447.919l5 1.25A2 2 0 0121 5.618v9.764a2 2 0 01-1.553 1.894L14 20m-5 0v-7m5 7v-7"/>
        </svg>
        <p class="text-lg font-semibold">Aucune parcelle trouvée</p>
        <p class="text-sm mt-1">Essayez de modifier vos filtres</p>
    </div>

    {{-- CARROUSEL --}}
    <div x-show="!chargement && parcelles.length > 0">

        {{-- Header carrousel --}}
        <div class="flex items-center justify-between mb-4">
            <p class="text-sm text-gray-500 dark:text-gray-400">
                <span x-text="total"></span> parcelle(s) trouvée(s)
            </p>
            <div class="flex items-center gap-2">
                {{-- Bouton précédent --}}
                <button
                    @click="precedent()"
                    :disabled="indexDebut === 0"
                    class="p-2 rounded-xl border border-gray-200 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700 disabled:opacity-40 disabled:cursor-not-allowed transition-colors duration-200"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                </button>

                {{-- Bouton suivant --}}
                <button
                    @click="suivant()"
                    :disabled="indexDebut + parPage >= parcelles.length && pageCourante >= dernierePage"
                    class="p-2 rounded-xl border border-gray-200 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700 disabled:opacity-40 disabled:cursor-not-allowed transition-colors duration-200"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </button>
            </div>
        </div>

        {{-- Cards --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            <template x-for="parcelle in parcellesVisibles" :key="parcelle.id">
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-md overflow-hidden hover:shadow-xl transition-shadow duration-300 flex flex-col">

                    {{-- Image --}}
                    <div class="relative h-48 bg-gray-100 dark:bg-gray-700">
                        <template x-if="imagePrincipale(parcelle)">
                            <img
                                :src="imagePrincipale(parcelle).url"
                                :alt="parcelle.titre"
                                class="w-full h-full object-cover"
                            />
                        </template>
                        <template x-if="!imagePrincipale(parcelle)">
                            <div class="w-full h-full flex flex-col items-center justify-center text-gray-400">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-12 h-12" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 9.75L12 3l9 6.75V21H3V9.75z"/>
                                </svg>
                                <span class="text-sm mt-2">Pas d'image</span>
                            </div>
                        </template>

                        {{-- Badge statut --}}
                        <span
                            class="absolute top-3 left-3 text-xs font-semibold px-2 py-1 rounded-full"
                            :class="badgeStatut(parcelle.statut)"
                            x-text="parcelle.statut.charAt(0).toUpperCase() + parcelle.statut.slice(1)"
                        ></span>

                        {{-- Badge viabilisée --}}
                        <template x-if="parcelle.viabilisee">
                            <span class="absolute top-3 right-3 text-xs font-semibold px-2 py-1 rounded-full bg-blue-100 text-blue-700 dark:bg-blue-900 dark:text-blue-300">
                                Viabilisée
                            </span>
                        </template>
                    </div>

                    {{-- Contenu --}}
                    <div class="p-4 flex flex-col gap-2 flex-1">
                        <h3 class="text-base font-bold text-gray-800 dark:text-gray-100 line-clamp-1" x-text="parcelle.titre"></h3>

                        <div class="flex items-center gap-1 text-gray-500 dark:text-gray-400 text-sm">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 21s-8-7.5-8-12a8 8 0 1116 0c0 4.5-8 12-8 12z"/>
                            </svg>
                            <span class="line-clamp-1" x-text="parcelle.quartier + ', ' + parcelle.ville"></span>
                        </div>

                        <div class="flex items-center justify-between mt-1">
                            <span class="text-sm text-gray-600 dark:text-gray-400" x-text="formatSuperficie(parcelle.superficie)"></span>
                            <span class="text-xs text-gray-400" x-text="parcelle.reference"></span>
                        </div>

                        <div class="mt-auto pt-3 border-t border-gray-100 dark:border-gray-700">
                            <p class="text-lg font-bold text-emerald-600 dark:text-emerald-400" x-text="formatPrix(parcelle.prix)"></p>
                        </div>

                        
                           <a :href="'/parcelles/' + parcelle.id"
                            class="mt-2 w-full bg-primary text-white text-sm font-semibold py-2 rounded-xl transition-colors duration-200 text-center block"
                        >
                            Voir les détails
                        </a>
                    </div>
                </div>
            </template>
        </div>

        {{-- Pagination points --}}
        <div class="flex justify-center gap-2 mt-6" x-show="dernierePage > 1">
            <template x-for="page in dernierePage" :key="page">
                <button
                    @click="allerPage(page)"
                    :class="page === pageCourante ? 'bg-emerald-600 w-3 h-3' : 'bg-gray-300 dark:bg-gray-600 w-2 h-2'"
                    class="rounded-full transition-all duration-200 self-center"
                ></button>
            </template>
        </div>

    </div>
</div>

{{-- Script Alpine --}}
<script>
function parcellesCarousel() {
    return {
        parcelles: [],
        chargement: false,
        total: 0,
        pageCourante: 1,
        dernierePage: 1,
        parPage: 8,
        indexDebut: 0,
        visibleCount: 4,
        filtres: {
            ville: '',
            quartier: '',
            statut: '',
            viabilisee: '',
            prix_min: '',
            prix_max: '',
            superficie_min: '',
        },

        get parcellesVisibles() {
            return this.parcelles.slice(this.indexDebut, this.indexDebut + this.visibleCount)
        },

        async chargerParcelles(page = 1) {
            this.chargement = true
            this.indexDebut = 0
            this.pageCourante = page

            const params = new URLSearchParams()
            params.append('page', page)
            params.append('per_page', this.parPage)

            Object.entries(this.filtres).forEach(([key, value]) => {
                if (value !== '') params.append(key, value)
            })

            try {
                const res = await fetch(`/api/parcelles?${params.toString()}`)
                const data = await res.json()
                this.parcelles = data.data
                this.total = data.total
                this.dernierePage = data.last_page
            } catch (e) {
                console.error('Erreur chargement parcelles:', e)
            } finally {
                this.chargement = false
            }
        },

        suivant() {
            if (this.indexDebut + this.visibleCount < this.parcelles.length) {
                this.indexDebut += this.visibleCount
            } else if (this.pageCourante < this.dernierePage) {
                this.chargerParcelles(this.pageCourante + 1)
            }
        },

        precedent() {
            if (this.indexDebut > 0) {
                this.indexDebut -= this.visibleCount
            }
        },

        allerPage(page) {
            this.chargerParcelles(page)
        },

        resetFiltres() {
            this.filtres = {
                ville: '',
                quartier: '',
                statut: '',
                viabilisee: '',
                prix_min: '',
                prix_max: '',
                superficie_min: '',
            }
            this.chargerParcelles()
        },

        imagePrincipale(parcelle) {
            return parcelle.images?.find(img => img.principale) ?? parcelle.images?.[0] ?? null
        },

        badgeStatut(statut) {
            const config = {
                'disponible': 'bg-green-100 text-green-700 dark:bg-green-900 dark:text-green-300',
                'vendu':      'bg-red-100 text-red-700 dark:bg-red-900 dark:text-red-300',
                'réservé':    'bg-yellow-100 text-yellow-700 dark:bg-yellow-900 dark:text-yellow-300',
            }
            return config[statut] ?? config['disponible']
        },

        formatPrix(prix) {
            return new Intl.NumberFormat('fr-FR').format(prix) + ' FCFA'
        },

        formatSuperficie(superficie) {
            return new Intl.NumberFormat('fr-FR').format(superficie) + ' m²'
        },
    }
}
</script>