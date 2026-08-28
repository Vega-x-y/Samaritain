{{-- Bandeau branding : entreprise + drapeau + pays (données active-conf PawaPay) --}}
@if(!empty($branding))
    <div class="flex items-center gap-3 rounded-xl bg-gray-50 dark:bg-gray-900/50 border border-gray-200 dark:border-gray-700 px-4 py-3">
        <img src="{{ asset('light_logo.svg') }}" alt="Samaritain" class="h-10 w-auto block dark:hidden">
        <img src="{{ asset('dark_logo.svg') }}" alt="Samaritain" class="hidden h-10 w-auto dark:block">
        <div class="min-w-0">
            @if(!empty($branding['companyName']))
                <p class="text-sm font-semibold text-gray-900 dark:text-white truncate">
                    {{ $branding['companyName'] }}
                </p>
            @endif
            <p class="text-xs text-gray-500 dark:text-gray-400 truncate">
                Paiement mobile
            </p>
        </div>
    </div>
@endif
