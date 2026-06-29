@props([
    'whatsapp' => null,
    'email' => null,
    'phone' => null,
    'label' => 'Contacter l\'agence',
    'property' => null
])

@php
    $popoverId = 'contact-popover-' . Str::random(8);
@endphp

<div 
    x-data="{ 
        open: false,
        placement: 'bottom',
        init() {
            this.$watch('open', (value) => {
                if (value) {
                    this.$nextTick(() => {
                        this.calculatePlacement();
                    });
                }
            });
        },
        calculatePlacement() {
            const button = this.$refs.button;
            const popover = this.$refs.popover;
            const rect = button.getBoundingClientRect();
            const spaceBelow = window.innerHeight - rect.bottom;
            const spaceAbove = rect.top;
            const popoverHeight = 200; // Hauteur approximative du popover
            
            if (spaceBelow < popoverHeight && spaceAbove > spaceBelow) {
                this.placement = 'top';
            } else {
                this.placement = 'bottom';
            }
        }
    }" 
    class="relative inline-block" 
>
    <button
        x-ref="button"
        type="button"
        @click="open = !open"
        @click.away="open = false"
        @keydown.escape="open = false"
        aria-haspopup="dialog"
        :aria-expanded="open.toString()"
        :aria-controls="open ? '{{ $popoverId }}' : null"
        class="inline-flex w-full items-center justify-center gap-x-1.5 shrink-0 transition-colors duration-100 text-sm/5 font-medium shadow-none rounded-[var(--radius)] bg-[var(--primary)] text-[var(--primary-foreground) hover:bg-[color-mix(in_oklab,var(--primary)_90%,transparent)] focus:bg-[color-mix(in_oklab,var(--primary)_90%,transparent)] active:bg-[var(--primary)] h-9 text-center px-4 py-2"
    >
        {{ $label }}
    </button>

    <div
        x-cloak
        x-ref="popover"
        x-show="open"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        id="{{ $popoverId }}"
        :class="{
            'absolute right-0 mt-2': placement === 'bottom',
            'absolute right-0 bottom-full mb-2': placement === 'top'
        }"
        class="w-64 z-50"
        role="dialog"
        aria-label="Options de contact"
    >
        <x-contact.popover 
            :whatsapp="$whatsapp" 
            :email="$email" 
            :property="$property"
            :phone="$phone" 
        />
    </div>
</div>