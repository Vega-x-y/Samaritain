@props([
    'label' => '',
    'name',
    'type' => 'text',
    'icon' => null,
    'placeholder' => '',
    'prefix' => null,
    'suffix' => null,
])

<div>
    @if($label)
        <label
            for="{{ $name }}"
            class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1"
        >
            {{ $label }}
        </label>
    @endif

    <div class="flex">
        @if($prefix)
            <span
                class="inline-flex items-center h-9 px-3 border border-r-0 border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-sm text-gray-500 dark:text-gray-400 rounded-l-lg"
            >
                {{ $prefix }}
            </span>
        @endif

        <div class="relative flex-1 min-w-0">
            @if($icon)
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <i data-lucide="{{ $icon }}" class="w-4 h-4 text-gray-400 dark:text-gray-500"></i>
                </div>
            @endif

            <input
                type="{{ $type }}"
                id="{{ $name }}"
                name="{{ $name }}"
                placeholder="{{ $placeholder }}"
                value="{{ old($name, $attributes->get('value')) }}"
                {{ $attributes->merge([
                    'class' => 'w-full h-9 text-sm border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 py-2 text-gray-800 dark:text-white focus:outline-hidden focus:ring-2 focus:border-primary dark:focus:border-primary focus:ring-primary/10 dark:focus:ring-primary/20 '
                        . ($icon ? 'pl-10' : 'pl-4')
                        . ' pr-4'
                        . ($prefix ? ' rounded-l-none' : ' rounded-l-lg')
                        . ($suffix ? ' rounded-r-none' : ' rounded-r-lg')
                ]) }}
            >
        </div>

        @if($suffix)
            <span
                class="inline-flex items-center h-9 px-3 border border-l-0 border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-sm text-gray-500 dark:text-gray-400 rounded-r-lg"
            >
                {{ $suffix }}
            </span>
        @endif
    </div>

    @error($name)
        <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
    @enderror
</div>
