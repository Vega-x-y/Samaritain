@props([
    'providers',
    'name' => 'provider',
    'label' => 'Opérateur',
])

<div>
    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-2">
        {{ $label }}
    </label>

    <div class="grid grid-cols-2 gap-3" role="radiogroup" aria-label="{{ $label }}">
        @foreach($providers as $provider)
            <label class="cursor-pointer">
                <input
                    type="radio"
                    name="{{ $name }}"
                    value="{{ $provider['provider'] }}"
                    class="peer sr-only"
                    required
                >
                <div class="flex flex-col items-center justify-center gap-2 h-24 px-3 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-center transition
                            hover:border-primary/50
                            peer-checked:border-primary peer-checked:ring-2 peer-checked:ring-primary/20">
                    @if(!empty($provider['logo']))
                        <img
                            src="{{ $provider['logo'] }}"
                            alt="{{ $provider['displayName'] }}"
                            loading="lazy"
                        >
                    @else
                        <i data-lucide="smartphone" class="w-6 h-6 text-gray-400 dark:text-gray-500"></i>
                    @endif
                </div>
            </label>
        @endforeach
    </div>

    @error($name)
        <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
    @enderror
</div>
