@props([
    'placeholder' => 'Rechercher…',
    'route' => null,
])

@php
    $action = $route ? route($route) : request()->url();
@endphp

<form method="GET" action="{{ $action }}" class="relative">
    @foreach (request()->query() as $key => $value)
        @if ($key !== 'search' && $key !== 'page')
            @if (is_array($value))
                @foreach ($value as $v)
                    <input type="hidden" name="{{ $key }}[]" value="{{ e($v) }}">
                @endforeach
            @else
                <input type="hidden" name="{{ $key }}" value="{{ e($value) }}">
            @endif
        @endif
    @endforeach

    <input type="text" name="search" value="{{ request('search') }}"
           placeholder="{{ $placeholder }}"
           class="w-full max-w-md pl-10 pr-4 py-2.5 text-sm border border-gray-200 dark:border-gray-700 rounded-lg focus:border-orange-500 focus:ring-2 focus:ring-orange-100 dark:focus:ring-orange-900 outline-none transition bg-white dark:bg-gray-800 text-gray-900 dark:text-white placeholder:text-gray-400 dark:placeholder:text-gray-400">

    <i data-lucide="search" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 dark:text-gray-400"></i>
</form>