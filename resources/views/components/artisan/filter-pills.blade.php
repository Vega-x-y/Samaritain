@props([
    'name',                     // nom du paramètre GET (ex: 'statut', 'type', 'categorie')
    'options',                  // ['val' => 'Label', ...] ou liste simple
    'allLabel' => 'Tous',
    'allIcon' => 'chart-no-axes-column',
    'icons' => [],              // ['val' => 'hammer', ...] icônes lucide optionnelles
    'value' => null,            // valeur courante (défaut : request($name))
    'activeClasses' => 'bg-gray-900 text-white border-gray-900 dark:bg-white dark:text-gray-900',
    'inactiveClasses' => 'bg-white dark:bg-gray-800 text-gray-500 dark:text-gray-400 border-gray-200 dark:border-gray-700 hover:border-primary',
    'count' => null,            // libellé de compteur optionnel (ex: "12 chantier(s)")
])

@php
    $current = $value ?? request($name);
    $pillClasses = 'inline-flex items-center gap-1.5 px-4 py-1.5 rounded-full text-sm font-medium border transition';
@endphp

<div class="flex flex-wrap gap-2 items-center">
    {{-- Pastille "Tous" : conserve la recherche, retire ce filtre et la pagination --}}
    <a href="{{ request()->fullUrlWithoutQuery([$name, 'page']) }}"
        class="{{ $pillClasses }} {{ ! $current ? $activeClasses : $inactiveClasses }}">
        <i data-lucide="{{ $allIcon }}" class="w-4 h-4"></i> {{ $allLabel }}
    </a>

    @foreach ($options as $option)
        @php
            $optionValue = match (true) {
                is_array($option) => $option['value'] ?? array_key_first($option),
                is_object($option) => $option->value ?? $option->id ?? $option,
                default => $option,
            };
            $label = match (true) {
                is_array($option) => $option['label'] ?? reset($option),
                is_object($option) && method_exists($option, 'label') => $option->label(),
                is_object($option) => $option->name ?? (string) $option,
                default => $option,
            };
            $icon = $icons[$optionValue] ?? null;
            $isActive = (string) $current === (string) $optionValue;
        @endphp
        <a href="{{ $isActive
                ? request()->fullUrlWithoutQuery([$name, 'page'])
                : request()->fullUrlWithQuery([$name => $optionValue, 'page' => null]) }}"
            class="{{ $pillClasses }} {{ $isActive ? $activeClasses : $inactiveClasses }}">
            @if ($icon)
                <i data-lucide="{{ $icon }}" class="w-4 h-4"></i>
            @endif
            {{ $label }}
        </a>
    @endforeach

    @if (filled($count))
        <span class="text-xs text-gray-400 dark:text-gray-500 ml-2">{{ $count }}</span>
    @endif
</div>
