{{-- Sélecteur d'opérateur : cartes cliquables avec logo (données active-conf PawaPay) --}}
<div>
    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-2">
        Opérateur
    </label>

    <div class="grid grid-cols-2 gap-3" role="radiogroup" aria-label="Opérateur">
        @foreach($branding['providers'] as $provider)
            <label class="cursor-pointer">
                <input
                    type="radio"
                    name="provider"
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
                            class="h-8 w-auto max-w-[80%] object-contain"
                            loading="lazy"
                        >
                    @else
                        <i data-lucide="smartphone" class="w-6 h-6 text-gray-400 dark:text-gray-500"></i>
                    @endif
                    <span class="text-xs font-medium text-gray-700 dark:text-gray-300 leading-tight">
                        {{ $provider['displayName'] }}
                    </span>
                </div>
            </label>
        @endforeach
    </div>

    @error('provider')
        <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
    @enderror
</div>
