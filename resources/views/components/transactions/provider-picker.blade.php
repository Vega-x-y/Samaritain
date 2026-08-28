@props([
    'providers',
    'name' => 'provider',
    'label' => 'Opérateur',
])

<div>
    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-2">
        {{ $label }}
    </label>

    <div class="grid grid-cols-2 sm:grid-cols-3 gap-3" role="radiogroup" aria-label="{{ $label }}">
        @foreach($providers as $provider)
            <label class="relative cursor-pointer">
                <input
                    type="radio"
                    name="{{ $name }}"
                    value="{{ $provider['provider'] }}"
                    class="peer sr-only"
                    required
                >
                <div class="flex flex-col items-center justify-center gap-1.5 h-24 px-3 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-center transition-all
                            hover:border-primary/50 hover:shadow-sm
                            peer-checked:border-primary peer-checked:ring-2 peer-checked:ring-primary/20 peer-checked:bg-primary/5
                            peer-focus-visible:ring-2 peer-focus-visible:ring-offset-2 peer-focus-visible:ring-primary">
                    @if(!empty($provider['logo']))
                        <img
                            src="{{ $provider['logo'] }}"
                            alt="{{ $provider['displayName'] }}"
                            loading="lazy"
                            class="h-16 w-auto max-w-[64px] object-contain"
                        >
                    @else
                        <i data-lucide="smartphone" class="w-6 h-6 text-gray-400 dark:text-gray-500"></i>
                    @endif

                    @if(!empty($provider['displayName']))
                        <span class="text-[11px] leading-none font-medium text-gray-600 dark:text-gray-300 truncate max-w-full">
                            {{ $provider['displayName'] }}
                        </span>
                    @endif
                </div>

                <div class="absolute -top-1.5 -right-1.5 hidden h-5 w-5 items-center justify-center rounded-full bg-primary text-white peer-checked:flex">
                    <i data-lucide="check" class="w-3 h-3"></i>
                </div>
            </label>
        @endforeach
    </div>

    @error($name)
        <p class="mt-1.5 text-xs text-red-600 dark:text-red-400 flex items-center gap-1">
            <i data-lucide="alert-circle" class="w-3.5 h-3.5"></i>
            {{ $message }}
        </p>
    @enderror
</div>