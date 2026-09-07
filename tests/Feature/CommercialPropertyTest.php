<?php

use App\Models\Boutique;
use App\Models\Bureau;
use App\Models\Property;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('separates boutiques and bureaux from residential listings', function () {
    Boutique::create([
        'name' => 'Boutique centre ville',
        'slug' => 'boutique-centre-ville',
        'is_active' => true,
        'is_verify' => true,
    ]);

    Bureau::create([
        'name' => 'Bureau centre ville',
        'slug' => 'bureau-centre-ville',
        'is_active' => true,
        'is_verify' => true,
    ]);

    $this->get('/boutiques')->assertOk()->assertSee('Boutique centre ville')->assertDontSee('Bureau centre ville');
    $this->get('/bureaux')->assertOk()->assertSee('Bureau centre ville')->assertDontSee('Boutique centre ville');
    $this->get('/properties')->assertOk()->assertDontSee('Boutique centre ville')->assertDontSee('Bureau centre ville');
});

it('rejects a commercial property through the residential detail route', function () {
    $property = Property::factory()->create(['property_type' => 'residential']);

    $this->get('/property/bureau-slug-invalide')->assertNotFound();
});
