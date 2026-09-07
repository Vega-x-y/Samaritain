<?php

use App\Models\Boutique;
use App\Models\City;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

test('public user can list active and verified boutiques', function () {
    $city = City::create(['name' => 'Kinshasa']);

    $verified = Boutique::create([
        'name' => 'Boutique Visible',
        'slug' => 'boutique-visible',
        'price' => 500000,
        'city_id' => $city->id,
        'is_active' => true,
        'is_verify' => true,
    ]);

    $unverified = Boutique::create([
        'name' => 'Boutique Cachee',
        'slug' => 'boutique-cachee',
        'price' => 300000,
        'city_id' => $city->id,
        'is_active' => true,
        'is_verify' => false,
    ]);

    $response = $this->get(route('boutique.index'));

    $response->assertOk();
    $response->assertSee('Boutique Visible');
    $response->assertDontSee('Boutique Cachee');
});

test('authenticated user can create a boutique with image', function () {
    Storage::fake('public');

    $user = User::factory()->create();
    $city = City::create(['name' => 'Kinshasa']);

    $response = $this->actingAs($user)->post(route('boutique.store'), [
        'name' => 'Ma Nouvelle Boutique',
        'description' => 'Description détaillée de la boutique',
        'price' => 750000,
        'price_type' => 'monthly',
        'surface' => 100,
        'city_id' => $city->id,
        'conditions' => '1',
        'images' => [
            UploadedFile::fake()->image('boutique.jpg'),
        ],
    ]);

    $response->assertRedirect(route('boutique.dashboard'));

    $this->assertDatabaseHas('boutiques', [
        'name' => 'Ma Nouvelle Boutique',
        'created_by' => $user->id,
        'is_verify' => false,
    ]);
});

test('owner can view their dashboard and pending boutique', function () {
    $user = User::factory()->create();

    $boutique = Boutique::create([
        'name' => 'Boutique Perso',
        'slug' => 'boutique-perso',
        'created_by' => $user->id,
        'is_active' => true,
        'is_verify' => false,
    ]);

    $response = $this->actingAs($user)->get(route('boutique.dashboard'));

    $response->assertOk();
    $response->assertSee('Boutique Perso');
});

test('admin can toggle boutique verification status', function () {
    $admin = User::factory()->create(['is_staff' => true]);

    $boutique = Boutique::create([
        'name' => 'Boutique a Verifier',
        'slug' => 'boutique-a-verifier',
        'created_by' => $admin->id,
        'is_active' => true,
        'is_verify' => false,
    ]);

    $response = $this->actingAs($admin)->patch(route('admin.boutique.toggle-verify', $boutique));

    $response->assertRedirect();
    $this->assertTrue($boutique->fresh()->is_verify);
});

