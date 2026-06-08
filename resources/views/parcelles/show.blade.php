{{-- resources/views/parcelles/show.blade.php --}}

@extends('layouts.base')
@section('title', 'Détail parcelle')

@section('content')
<x-blade-components::layout.container>

    <div
        x-data="parcelleDetail({{ $id }})"
        x-init="charger()"
        class="max-w-4xl mx-auto py-8"
    >
        {{-- Retour --}}
        <a href="{{ route('parcelles.index') }}" class="flex items-center gap-2 text-gray-500 hover:text-gray-800 mb-6 transition-colors">
            <i data-lucide="chevron-left" class="w-5 h-5"></i>
            Retour aux parcelles
        </a>

        {{-- Chargement --}}
        <div x-show="chargement" class="flex justify-center items-center py-16">
            <svg class="animate-spin w-8 h-8 text-emerald-500" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
            </svg>
        </div>

        {{-- Contenu --}}
        <div x-show="!chargement && parcelle">

            {{-- Images --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mb-6 rounded-2xl overflow-hidden">
                <template x-if="images.length > 0">
                    <img :src="images[0].url" :alt="parcelle.titre" class="w-full h-72 object-cover rounded-2xl" />
                </template>
                <template x-if="images.length === 0">
                    <div class="w-full h-72 bg-gray-100 rounded-2xl flex items-center justify-center text-gray-400">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-16 h-16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 9.75L12 3l9 6.75V21H3V9.75z"/>
                        </svg>
                    </div>
                </template>
            </div>

            {{-- Infos principales --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 mb-4">
                <div class="flex items-start justify-between mb-4">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-800" x-text="parcelle.titre"></h1>
                        <div class="flex items-center gap-1 text-gray-500 text-sm mt-1">
                            <i data-lucide="map-pin" class="w-4 h-4"></i>
                            <span x-text="parcelle.quartier + ', ' + parcelle.ville"></span>
                        </div>
                    </div>

                    {{-- Badge statut --}}
                    <span
                        class="text-xs font-semibold px-3 py-1 rounded-full"
                        :class="badgeStatut(parcelle.statut)"
                        x-text="parcelle.statut"
                    ></span>
                </div>

                {{-- Prix --}}
                <p class="text-3xl font-bold text-emerald-600 mb-6" x-text="formatPrix(parcelle.prix)"></p>

                {{-- Détails --}}
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div class="bg-gray-50 rounded-xl p-3 text-center">
                        <p class="text-xs text-gray-500 mb-1">Superficie</p>
                        <p class="font-bold text-gray-800" x-text="formatSuperficie(parcelle.superficie)"></p>
                    </div>
                    <div class="bg-gray-50 rounded-xl p-3 text-center">
                        <p class="text-xs text-gray-500 mb-1">Référence</p>
                        <p class="font-bold text-gray-800" x-text="parcelle.reference"></p>
                    </div>
                    <div class="bg-gray-50 rounded-xl p-3 text-center">
                        <p class="text-xs text-gray-500 mb-1">Viabilisée</p>
                        <p class="font-bold" :class="parcelle.viabilisee ? 'text-emerald-600' : 'text-red-500'" x-text="parcelle.viabilisee ? 'Oui' : 'Non'"></p>
                    </div>
                    <div class="bg-gray-50 rounded-xl p-3 text-center">
                        <p class="text-xs text-gray-500 mb-1">Titre foncier</p>
                        <p class="font-bold text-gray-800" x-text="parcelle.titre_foncier || 'N/A'"></p>
                    </div>
                </div>
            </div>

            {{-- Description --}}
            <div x-show="parcelle.description" class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 mb-4">
                <h2 class="text-lg font-bold text-gray-800 mb-2">Description</h2>
                <p class="text-gray-600 text-sm leading-relaxed" x-text="parcelle.description"></p>
            </div>

            {{-- Actions --}}
            <div class="flex items-center gap-3">
                
                   <a :href="'/parcelles/' + parcelle.id + '/edit'"
                    class="bg-emerald-600 hover:bg-emerald-700 text-white font-semibold px-6 py-2.5 rounded-xl transition-colors duration-200 flex items-center gap-2"
                >
                    <i data-lucide="pencil" class="w-4 h-4"></i>
                    Modifier
                </a>
                <button
                    @click="supprimer()"
                    class="bg-red-50 hover:bg-red-100 text-red-600 font-semibold px-6 py-2.5 rounded-xl transition-colors duration-200 flex items-center gap-2"
                >
                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                    Supprimer
                </button>
            </div>

        </div>
    </div>

</x-blade-components::layout.container>

<script>
function parcelleDetail(id) {
    return {
        id: id,
        parcelle: null,
        images: [],
        chargement: false,

        async charger() {
            this.chargement = true
            try {
                const res = await fetch(`/api/parcelles/${this.id}`)
                const data = await res.json()
                this.parcelle = data
                this.images = data.images || []
            } catch(e) {
                console.error('Erreur:', e)
            } finally {
                this.chargement = false
            }
        },

        async supprimer() {
            if (!confirm('Êtes-vous sûr de vouloir supprimer cette parcelle ?')) return
            try {
                const res = await fetch(`/api/parcelles/${this.id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    }
                })
                if (res.ok) window.location.href = '/parcelles'
            } catch(e) {
                console.error('Erreur suppression:', e)
            }
        },

        badgeStatut(statut) {
            const config = {
                'disponible': 'bg-green-100 text-green-700',
                'vendu':      'bg-red-100 text-red-700',
                'réservé':    'bg-yellow-100 text-yellow-700',
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

@endsection