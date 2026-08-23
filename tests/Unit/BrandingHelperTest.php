<?php

use App\Support\BrandingHelper;

test('getEncodedImages retourne les images encodées', function () {
    $images = BrandingHelper::getEncodedImages();

    expect($images)
        ->toBeArray()
        ->toHaveKeys(['logoBase64', 'waveBase64']);
});

test('getColorForDocument retourne la bonne couleur', function () {
    expect(BrandingHelper::getColorForDocument('devis'))->toBe('#f47920');
    expect(BrandingHelper::getColorForDocument('facture'))->toBe('#f47920');
    expect(BrandingHelper::getColorForDocument('signed'))->toBe('#10b981');
});

test('getBackgroundForDocument retourne le bon fond', function () {
    expect(BrandingHelper::getBackgroundForDocument('devis'))->toBe('#fff8f0');
    expect(BrandingHelper::getBackgroundForDocument('signed'))->toBe('#f0fdf4');
});

test('getDataForDocument retourne toutes les données', function () {
    $data = BrandingHelper::getDataForDocument('devis');

    expect($data)
        ->toBeArray()
        ->toHaveKeys(['logoBase64', 'waveBase64', 'accentColor', 'accentBgColor', 'slogan'])
        ->and($data['accentColor'])->toBe('#f47920')
        ->and($data['accentBgColor'])->toBe('#fff8f0')
        ->and($data['slogan'])->toBe('VIVEZ SEREINEMENT');
});
