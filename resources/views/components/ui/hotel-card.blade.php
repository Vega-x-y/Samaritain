@props([
    'hotel' => null,
])

<a href="{{ route('hotel.show', $hotel) }}"
    class="group block w-full cursor-pointer">

    {{-- Image --}}
    <div class="relative overflow-hidden rounded-2xl bg-gray-100 aspect-[4/3]">
        @if($hotel->images->isNotEmpty())
            <img src="{{ $hotel->images->first()->image_url }}" alt="{{ $hotel->title }}"
                class="w-full h-full object-cover shadow-sm transition duration-500 group-hover:scale-105">
        @else
            <div class="w-full h-full flex items-center justify-center">
                <i data-lucide="building-2" class="w-12 h-12 text-gray-400"></i>
            </div>
        @endif

        {{-- Overlay gradient --}}
        <div
            class="absolute inset-0 bg-gradient-to-t from-black/30 via-transparent to-transparent opacity-100 md:opacity-0 md:group-hover:opacity-100 transition duration-300">
        </div>

        @if ($hotel->is_verify)
            <span
                class="absolute bottom-2.5 left-2.5 flex items-center gap-1 bg-white/90 backdrop-blur-sm text-green-600 text-xs font-medium px-2.5 py-1 rounded-full shadow-sm">
                <i data-lucide="badge-check" class="w-3 h-3"></i>
                vérifié
            </span>
        @else
            <span
                class="absolute bottom-2.5 left-2.5 flex items-center gap-1 bg-white/90 backdrop-blur-sm text-amber-600 text-xs font-medium px-2.5 py-1 rounded-full shadow-sm">
                <i data-lucide="hourglass" class="w-3 h-3"></i>
                en attente
            </span>
        @endif

        {{-- Affichage du nombre de vues sur l'image --}}
        <div class="absolute bottom-2.5 right-2.5 flex items-center gap-1 bg-black/50 backdrop-blur-sm text-white text-xs font-medium px-2 py-1 rounded-full shadow-sm">
            <i data-lucide="eye" class="w-3 h-3"></i>
            {{ number_format($hotel->views) }}
        </div>
    </div>

    {{-- Infos --}}
    <div class="mt-3 space-y-1.5 px-0.5">
        <div class="flex items-start justify-between gap-2">
            <div class="flex-1 min-w-0">
                <div class="flex items-center justify-between">
                    <h3 class="text-xs sm:text-sm font-semibold text-gray-800 dark:text-gray-300 truncate group-hover:text-primary transition">
                        {{ $hotel->title }}
                    </h3>
                    <div class="text-gray-400 text-[11px] sm:text-xs flex items-center gap-1 shrink-0 ml-2">
                        <i data-lucide="star" class="h-2.5 w-2.5 sm:h-3 sm:w-3"></i>
                        {{ $hotel->star_rating }}
                    </div>
                </div>

                <p class="text-gray-400 text-xs flex items-center gap-1 mt-0.5">
                    <i data-lucide="map-pin" class="w-3 h-3 flex-shrink-0"></i>
                    {{ $hotel->arrondissement->name ?? '' }}, {{ $hotel->city->name }}
                </p>
            </div>
        </div>

        <p class="text-gray-500 text-xs leading-relaxed line-clamp-1">
            {{ $hotel->description }}
        </p>

        <div class="flex items-center justify-between pt-1">
            <p class="text-gray-800 dark:text-gray-300 text-xs font-bold">
                {{ number_format($hotel->price_per_hour, 0, ',', ' ') }} FCFA <span class="text-xs font-normal text-gray-400">{{ $hotel->price_label }}</span>
            </p>
            <div class="flex items-center gap-3">
                <span
                    class="text-xs text-primary font-medium opacity-0 group-hover:opacity-100 transition flex items-center gap-0.5">
                    Voir
                    <i data-lucide="arrow-right" class="w-3 h-3"></i>
                </span>
            </div>
        </div>
    </div>
</a>