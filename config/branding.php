<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Couleurs de branding Samaritain
    |--------------------------------------------------------------------------
    |
    | Ces couleurs sont utilisées dans tous les PDFs générés par l'application.
    | Modifier ces valeurs impactera tous les documents générés.
    |
    */

    'colors' => [
        'primary' => '#f47920',      // Orange principal
        'success' => '#10b981',      // Vert succès
        'danger' => '#dc2626',       // Rouge danger
    ],

    'backgrounds' => [
        'primary' => '#fff8f0',      // Orange clair
        'success' => '#f0fdf4',      // Vert clair
        'danger' => '#fef2f2',       // Rouge clair
    ],

    /*
    |--------------------------------------------------------------------------
    | Images de branding
    |--------------------------------------------------------------------------
    |
    | Chemins relatifs depuis public_path()
    |
    */

    'images' => [
        'logo' => 'images/logo-samaritain.png',
        'wave' => 'images/header-wave.png',
    ],

    /*
    |--------------------------------------------------------------------------
    | Slogan
    |--------------------------------------------------------------------------
    */

    'slogan' => 'VIVEZ SEREINEMENT',

    /*
    |--------------------------------------------------------------------------
    | Couleurs par type de document
    |--------------------------------------------------------------------------
    |
    | Définit quelle couleur utiliser pour chaque type de document
    |
    */

    'document_colors' => [
        'devis' => 'primary',
        'facture' => 'primary',
        'attestation' => 'primary',
        'compte_rendu' => 'primary',
        'signed' => 'success',
    ],
];
