@props([
    'whatsapp' => null,
    'email' => true,
    'phone' => null,
    'property' => null
])

<div class="bg-card border border-border rounded-lg shadow-lg overflow-hidden p-1">
    <div class="py-1" role="menu" aria-orientation="vertical">
        @if($whatsapp)
            <x-contact.item
                icon="whatsapp"
                title="WhatsApp"
                description="Envoyez-nous un message"
                :href="$whatsapp"
                role="menuitem"
            />
        @endif

        @if($email)
            <x-contact.item
                icon="mail"
                title="Email"
                description="Écrivez-nous"
                href="#"
                role="menuitem"
            />
        @endif

        @if($phone)
            <x-contact.item
                icon="phone"
                title="Téléphone"
                description="Appelez-nous"
                :href="'tel:' . $phone"
                role="menuitem"
            />
        @endif
    </div>
</div>