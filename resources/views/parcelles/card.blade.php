{{-- resources/views/components/parcelles/card.blade.php --}}

@props(['parcelle'])

@php
    $imagePrincipale = collect($parcelle['images'] ?? [])->firstWhere('principale', true)
        ?? collect($parcelle['images'] ?? [])->first();

    $statutConfig = [
        'disponible' => ['label' => 'Disponible', 'class' => 'bg-green-100 text-green-700 dark:bg-green-900 dark:text-green-300'],
        'vendu'      => ['label' => 'Vendu',      'class' => 'bg-red-100 text-red-700 dark:bg-red-900 dark:text-red-300'],
        'réservé'    => ['label' => 'Réservé',    'class' => 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900 dark:text-yellow-300'],
    ];

    $statut = $statutConfig[$parcelle['statut']] ?? $statutConfig['disponible'];

    $prix = number_format($parcelle['prix'], 0, ',', ' ') . ' FCFA';
    $superficie = number_format($parcelle['superficie'], 0, ',', ' ') . ' m²';
@endphp

<div class="bg-white dark:bg-gray-800 rounded-2xl shadow-md overflow-hidden hover:shadow-xl transition-shadow duration-300 flex flex-col">

    {{-- Image --}}
    <div class="relative h-48 bg-gray-100 dark:bg-gray-700">
        @if($imagePrincipale)
            <img
                src="{{ $imagePrincipale['url'] }}"
                alt="{{ $parcelle['titre'] }}"
                class="w-full h-full object-cover"
                onerror="this.parentElement.innerHTML='<div class=\'w-full h-full flex flex-col items-center justify-center text-gray-400\'><svg xmlns=\'http://www.w3.org/2000/svg\' class=\'w-12 h-12\' fill=\'none\' viewBox=\'0 0 24 24\' stroke=\'currentColor\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'1.5\' d=\'M3 9.75L12 3l9 6.75V21H3V9.75z\'/></svg><span class=\'text-sm mt-2\'>Pas d\'image</span></div>'"
            />
        @else
            <div class="w-full h-full flex flex-col items-center justify-center text-gray-400">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-12 h-12" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 9.75L12 3l9 6.75V21H3V9.75z"/>
                </svg>
                <span class="text-sm mt-2">Pas d'image</span>
            </div>
        @endif

        {{-- Badge statut --}}
        <span class="absolute top-3 left-3 text-xs font-semibold px-2 py-1 rounded-full {{ $statut['class'] }}">
            {{ $statut['label'] }}
        </span>

        {{-- Badge viabilisée --}}
        @if($parcelle['viabilisee'])
            <span class="absolute top-3 right-3 text-xs font-semibold px-2 py-1 rounded-full bg-blue-100 text-blue-700 dark:bg-blue-900 dark:text-blue-300">
                Viabilisée
            </span>
        @endif
    </div>

    {{-- Contenu --}}
    <div class="p-4 flex flex-col gap-2 flex-1">

        {{-- Titre --}}
        <h3 class="text-base font-bold text-gray-800 dark:text-gray-100 line-clamp-1">
            {{ $parcelle['titre'] }}
        </h3>

        {{-- Localisation --}}
        <div class="flex items-center gap-1 text-gray-500 dark:text-gray-400 text-sm">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 21s-8-7.5-8-12a8 8 0 1116 0c0 4.5-8 12-8 12z"/>
                <circle cx="12" cy="9" r="2.5" stroke="currentColor" stroke-width="2" fill="none"/>
            </svg>
            <span class="line-clamp-1">{{ $parcelle['quartier'] }}, {{ $parcelle['ville'] }}</span>
        </div>

        {{-- Superficie & Référence --}}
        <div class="flex items-center justify-between mt-1">
            <div class="flex items-center gap-1 text-gray-600 dark:text-gray-400 text-sm">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4h16v16H4z"/>
                </svg>
                <span>{{ $superficie }}</span>
            </div>
            <span class="text-xs text-gray-400">{{ $parcelle['reference'] }}</span>
        </div>

        {{-- Prix --}}
        <div class="mt-auto pt-3 border-t border-gray-100 dark:border-gray-700">
            <p class="text-lg font-bold text-emerald-600 dark:text-emerald-400">
                {{ $prix }}
            </p>
        </div>

        {{-- Bouton --}}
        
    <a href="#" class="mt-2 w-full bg-primary hover:bg-primary text-white text-sm font-semibold py-2 rounded-xl transition-colors duration-200 text-center block" >
            Voir les détails
        </a>

    </div>
</div>