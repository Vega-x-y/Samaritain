<?php

return [

    /*
    |--------------------------------------------------------------------------
    | WhatsApp Contact Number
    |--------------------------------------------------------------------------
    |
    | Le numéro WhatsApp de contact de l'entreprise.
    | Format international recommandé: +243 XXX XXX XXX
    |
    */

    'whatsapp' => env('CONTACT_WHATSAPP', '+243 000 000 000'),

    /*
    |--------------------------------------------------------------------------
    | Email de contact
    |--------------------------------------------------------------------------
    |
    | L'adresse email principale de contact. Par défaut, utilise l'email
    | configuré dans mail.from.address
    |
    */

    'email' => env('CONTACT_EMAIL', env('MAIL_FROM_ADDRESS', 'support@kudia.lekori.com')),

    /*
    |--------------------------------------------------------------------------
    | Horaires de disponibilité
    |--------------------------------------------------------------------------
    |
    | Les horaires de disponibilité de l'équipe support
    |
    */

    'hours' => [
        'weekdays' => '8h00 - 18h00',
        'saturday' => '9h00 - 14h00',
        'sunday' => 'Fermé',
    ],

];
