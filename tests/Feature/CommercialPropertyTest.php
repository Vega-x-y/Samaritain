<?php

use App\Models\Property;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('separates boutiques and bureaux from residential listings', function () {
    Property::factory()->create([
        'property_type' => 'boutique',
        'is_active' => true,
        'is_verify' => true,
        'title' => 'Boutique centre ville',
    ]);
    Property::factory()->create([
        'property_type' => 'bureau',
        'is_active' => true,
        'is_verify' => true,
        'title' => 'Bureau centre ville',
    ]);

    $this->get('/boutiques')->assertOk()->assertSee('Boutique centre ville')->assertDontSee('Bureau centre ville');
    $this->get('/bureaux')->assertOk()->assertSee('Bureau centre ville')->assertDontSee('Boutique centre ville');
    $this->get('/properties')->assertOk()->assertDontSee('Boutique centre ville')->assertDontSee('Bureau centre ville');
});

it('rejects a commercial property through the residential detail route', function () {
    $property = Property::factory()->create(['property_type' => 'boutique']);

    $this->get('/property/'.$property->slug)->assertNotFound();
});
