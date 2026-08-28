{{-- Bandeau branding : entreprise + drapeau + pays (données active-conf PawaPay) --}}
@if(!empty($branding))
    <div class="flex items-center gap-3 rounded-xl bg-gray-50 dark:bg-gray-900/50 border border-gray-200 dark:border-gray-700 px-4 py-3">
        <img src="{{ asset('light_logo.svg') }}" alt="Samaritain" class="h-8 w-auto block dark:hidden shrink-0">
        <img src="{{ asset('dark_logo.svg') }}" alt="Samaritain" class="hidden h-8 w-auto dark:block shrink-0">

        <div class="min-w-0 flex-1">
            @if(!empty($branding['companyName']))
                <p class="text-sm font-semibold text-gray-900 dark:text-white truncate">
                    {{ $branding['companyName'] }}
                </p>
            @endif
            <p class="text-xs text-gray-500 dark:text-gray-400 truncate">
                Paiement mobile
            </p>
        </div>

        @if(!empty($branding['countryFlag']) || !empty($branding['countryName']))
            <div class="flex items-center gap-1.5 shrink-0 rounded-full bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 px-2.5 py-1">
                @if(!empty($branding['countryFlag']))
                    <span class="text-sm leading-none">{{ $branding['countryFlag'] }}</span>
                @endif
                @if(!empty($branding['countryName']))
                    <span class="text-xs font-medium text-gray-600 dark:text-gray-300">{{ $branding['countryName'] }}</span>
                @endif
            </div>
        @endif
    </div>
@endif