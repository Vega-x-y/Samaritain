@props([
    'filters' => [],           // [['name' => 'type', 'label' => 'Type', 'value' => ..., 'labels' => ['a' => 'A']], ...]
    'total' => null,           // libellé optionnel de compteur (ex: "12 chantier(s)")
    'totalLabel' => null,
])

@php
    $chipClasses = 'inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-medium text-primary dark:text-primary-400 bg-primary/10 dark:bg-primary/20 border border-primary/20 dark:border-primary/30';
@endphp

@if (collect($filters)->contains(fn ($filter) => filled($filter['value'] ?? request($filter['name']))))
    <div class="flex flex-wrap gap-2 items-center">
        <span class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Filtres actifs :</span>

        @foreach ($filters as $filter)
            @php
                $value = $filter['value'] ?? request($filter['name']);
                $display = ($filter['labels'] ?? [])[$value] ?? $value;
            @endphp
            @if (filled($value))
                <a href="{{ request()->fullUrlWithoutQuery([$filter['name'], 'page']) }}"
                    class="{{ $chipClasses }}" title="Retirer ce filtre">
                    {{ $filter['label'] }} : {{ $display }}
                    <i data-lucide="x" class="w-3.5 h-3.5"></i>
                </a>
            @endif
        @endforeach

        {{-- Réinitialisation globale : retire tous les paramètres de requête --}}
        <a href="{{ request()->fullUrlWithoutQuery(['page']) }}"
            class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-medium text-gray-500 dark:text-gray-400 border border-gray-200 dark:border-gray-700 hover:border-primary hover:text-primary transition">
            <i data-lucide="rotate-ccw" class="w-3.5 h-3.5"></i> Tout réinitialiser
        </a>

        @if (filled($total))
            <span class="text-xs text-gray-400 dark:text-gray-500">{{ $total }}{{ $totalLabel ? ' '.$totalLabel : '' }}</span>
        @endif
    </div>
@endif
