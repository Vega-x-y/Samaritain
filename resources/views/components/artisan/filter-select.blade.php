@props([
    'name',                     // nom du paramètre GET (ex: 'client_id', 'chantier_id')
    'label',
    'options',                  // [id => 'Label', ...] ou liste de modèles (voir optionValue/optionLabel)
    'placeholder' => 'Tous',
    'optionValue' => null,      // propriété/clé à utiliser comme valeur (ex: 'id')
    'optionLabel' => null,      // propriété/clé à utiliser comme libellé (ex: 'nom')
    'value' => null,            // valeur courante (défaut : request($name))
    'persist' => [],            // paramètres à garder en hidden inputs (ex: ['search'])
    'exclude' => null,          // paramètres à supprimer lors de la soumission (défaut : tous les autres)
    'autoSubmit' => true,       // soumet le formulaire au changement
    'hint' => null,             // texte d'aide optionnel
])

@php
    $current = $value ?? request($name);
    // Par défaut, on conserve TOUS les autres paramètres de la requête (recherche,
    // autres filtres) via des hidden inputs — même logique que la search-bar.
    $persistKeys = collect($persist !== null ? $persist : array_keys(request()->query()))
        ->reject(fn ($key) => $key === $name || $key === 'page')
        ->reject(fn ($key) => is_array($exclude) && in_array($key, $exclude))
        ->unique()
        ->values();

    $actionUrl = $exclude === null
        ? request()->url()
        : request()->fullUrlWithoutQuery(array_merge($exclude, [$name, 'page']));

    $resolve = function ($option) use ($optionValue, $optionLabel) {
        if (is_array($option)) {
            return [
                'value' => $optionValue !== null ? data_get($option, $optionValue) : array_key_first($option),
                'label' => $optionLabel !== null ? data_get($option, $optionLabel) : reset($option),
            ];
        }

        if (is_object($option)) {
            $value = $optionValue !== null ? $option->{$optionValue} : ($option->value ?? $option->id);
            $label = $optionLabel !== null ? $option->{$optionLabel} : ($option->label ?? $option->name ?? $value);
            return ['value' => $value, 'label' => $label];
        }

        return ['value' => $option, 'label' => $option];
    };
@endphp

<form method="GET" action="{{ $actionUrl }}" @if ($autoSubmit) onchange="this.submit()" @endif>
    {{-- Hidden inputs : conserve la recherche et les autres filtres --}}
    @foreach ($persistKeys as $persisted)
        @if (is_array(request($persisted)))
            @foreach (request($persisted) as $persistedValue)
                <input type="hidden" name="{{ $persisted }}[]" value="{{ e($persistedValue) }}">
            @endforeach
        @elseif (request()->filled($persisted))
            <input type="hidden" name="{{ $persisted }}" value="{{ request($persisted) }}">
        @endif
    @endforeach

    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1" for="filter-{{ $name }}">
        {{ $label }}
    </label>
    <select id="filter-{{ $name }}" name="{{ $name }}"
        class="w-full px-4 py-2.5 border border-gray-200 dark:border-gray-700 rounded-lg focus:border-primary focus:ring-2 focus:ring-primary/10 outline-none transition bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm">
        <option value="">{{ $placeholder }}</option>
        @foreach ($options as $option)
            @php $resolved = $resolve($option); @endphp
            <option value="{{ $resolved['value'] }}" @selected((string) $current === (string) $resolved['value'])>
                {{ $resolved['label'] }}
            </option>
        @endforeach
    </select>
    @if ($hint)
        <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">{{ $hint }}</p>
    @endif
</form>
