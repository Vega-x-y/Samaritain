<?php

use App\Models\Bureau;
use App\Models\City;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

test('public user can list active and verified bureaux', function () {
    $city = City::create(['name' => 'Kinshasa']);

    $verified = Bureau::create([
        'name' => 'Bureau Visible',
        'slug' => 'bureau-visible',
        'price' => 600000,
        'city_id' => $city->id,
        'is_active' => true,
        'is_verify' => true,
    ]);

    $unverified = Bureau::create([
        'name' => 'Bureau Cache',
        'slug' => 'bureau-cache',
        'price' => 400000,
        'city_id' => $city->id,
        'is_active' => true,
        'is_verify' => false,
    ]);

    $response = $this->get(route('bureau.index'));

    $response->assertOk();
    $response->assertSee('Bureau Visible');
    $response->assertDontSee('Bureau Cache');
});

test('authenticated user can create a bureau with image', function () {
    Storage::fake('public');

    $user = User::factory()->create();
    $city = City::create(['name' => 'Kinshasa']);

    $response = $this->actingAs($user)->post(route('bureau.store'), [
        'name' => 'Nouveau Bureau Executive',
        'description' => 'Superbe bureau fonctionnel avec vue',
        'price' => 1000000,
        'price_type' => 'monthly',
        'surface' => 150,
        'rooms' => 4,
        'city_id' => $city->id,
        'conditions' => '1',
        'images' => [
            UploadedFile::fake()->image('bureau.jpg'),
        ],
    ]);

    $response->assertRedirect(route('bureau.dashboard'));

    $this->assertDatabaseHas('bureaus', [
        'name' => 'Nouveau Bureau Executive',
        'created_by' => $user->id,
        'is_verify' => false,
    ]);
});

test('owner can view their dashboard and pending bureau', function () {
    $user = User::factory()->create();

    $bureau = Bureau::create([
        'name' => 'Bureau Perso',
        'slug' => 'bureau-perso',
        'created_by' => $user->id,
        'is_active' => true,
        'is_verify' => false,
    ]);

    $response = $this->actingAs($user)->get(route('bureau.dashboard'));

    $response->assertOk();
    $response->assertSee('Bureau Perso');
});

test('admin can toggle bureau verification status', function () {
    $admin = User::factory()->create(['is_staff' => true]);

    $bureau = Bureau::create([
        'name' => 'Bureau a Verifier',
        'slug' => 'bureau-a-verifier',
        'created_by' => $admin->id,
        'is_active' => true,
        'is_verify' => false,
    ]);

    $response = $this->actingAs($admin)->patch(route('admin.bureau.toggle-verify', $bureau));

    $response->assertRedirect();
    $this->assertTrue($bureau->fresh()->is_verify);
});

