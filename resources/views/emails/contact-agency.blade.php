<x-mail::layout>
{{-- Header personnalisé --}}
<x-slot:header>
    <x-mail::header :url="config('app.url')">
        {{ config('app.name') }}
    </x-mail::header>
</x-slot:header>

# Nouveau message de {{ $senderName }}

**Référence annonce :** {{ $reference }}  
**Lien :** [Voir l'annonce]({{ $annonceUrl }})  

---

## Détails de l'expéditeur

- **Nom :** {{ $senderName }}
- **Email :** {{ $senderEmail }}
@if($senderPhone)
- **Téléphone :** {{ $senderPhone }}
@endif
- **Date d'envoi :** {{ $date }}

---

## Sujet

{{ $subject }}

---

## Message

{{ $messageBody }}

---

<x-mail::button :url="$annonceUrl" color="primary">
    Voir l'annonce
</x-mail::button>

<x-slot:footer>
    <x-mail::footer>
        © {{ date('Y') }} {{ config('app.name') }}. Tous droits réservés.
    </x-mail::footer>
</x-slot:footer>
</x-mail::layout>