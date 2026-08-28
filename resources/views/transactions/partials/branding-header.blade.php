{{-- Bandeau branding : entreprise + drapeau + pays (données active-conf PawaPay) --}}
@if(!empty($branding))
    <div class="flex items-center gap-3 rounded-xl bg-gray-50 dark:bg-gray-900/50 border border-gray-200 dark:border-gray-700 px-4 py-3">
        @if(!empty($branding['flag']))
            <img
                src="{{ $branding['flag'] }}"
                alt="{{ $branding['countryName'] ?? 'Drapeau' }}"
                class="w-8 h-8 rounded-full object-cover border border-gray-200 dark:border-gray-600"
                loading="lazy"
            >
        @endif
        <div class="min-w-0">
            @if(!empty($branding['companyName']))
                <p class="text-sm font-semibold text-gray-900 dark:text-white truncate">
                    {{ $branding['companyName'] }}
                </p>
            @endif
            <p class="text-xs text-gray-500 dark:text-gray-400 truncate">
                Paiement mobile money{{ !empty($branding['countryName']) ? ' — '.$branding['countryName'] : '' }}
            </p>
        </div>
    </div>
@endif
