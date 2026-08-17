<?php

use App\Models\City;
use App\Models\Property;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('loads property search page without error', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get('/properties/search');

    $response->assertStatus(200);
});

it('handles invalid city_id parameter gracefully', function () {
    $user = User::factory()->create();

    // Test with a non-existent city_id
    $response = $this->actingAs($user)->get('/properties/search?city_id=99999');

    $response->assertStatus(200);
});

it('handles valid city_id parameter', function () {
    $user = User::factory()->create();
    $city = City::factory()->create();

    $response = $this->actingAs($user)->get('/properties/search?city_id='.$city->id);

    $response->assertStatus(200);
});

it('displays properties on search page', function () {
    $user = User::factory()->create();
    Property::factory()->count(3)->create(['is_active' => true, 'is_verify' => true]);

    $response = $this->actingAs($user)->get('/properties/search');

    $response->assertStatus(200);
    $response->assertSee('bien(s) trouvé(s)');
});
