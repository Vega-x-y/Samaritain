<?php

use App\Models\Amenity;
use App\Models\Arrondissement;
use App\Models\City;
use App\Models\Hotel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(function () {
    Storage::fake('r2');

    $this->user = User::factory()->create();

    $this->city = City::create(['name' => 'Brazzaville']);
    $this->arrondissement = Arrondissement::create(['name' => 'Bacongo', 'city_id' => $this->city->id]);
    $this->amenity = Amenity::create(['name' => 'Wi-Fi']);
});

test('hotels index page loads successfully', function () {
    Hotel::factory()->create([
        'created_by' => $this->user->id,
        'city_id' => $this->city->id,
        'is_verify' => true,
        'is_active' => true,
    ]);

    $this->get(route('hotel.index'))
        ->assertOk()
        ->assertSee('Nos hôtels');
});

test('a user can view the hotel create page', function () {
    $this->actingAs($this->user)
        ->get(route('hotel.create'))
        ->assertOk()
        ->assertSee('Publier un hôtel');
});

test('a user can store a hotel', function () {
    $this->actingAs($this->user)
        ->post(route('hotel.store'), [
            'title' => 'Hôtel Le Palais Royal',
            'description' => 'Un hôtel de luxe au cœur de la ville.',
            'price_per_night' => 50000,
            'price_per_hour' => 5000,
            'star_rating' => 5,
            'rooms' => 10,
            'bathrooms' => 5,
            'furnished' => true,
            'address' => '12 Avenue de la Paix',
            'city_id' => $this->city->id,
            'arrondissement_id' => $this->arrondissement->id,
            'amenities' => [$this->amenity->id],
            'images' => [UploadedFile::fake()->image('hotel.jpg', 800, 600)],
            'conditions' => '1',
        ])
        ->assertRedirect(route('hotel.dashboard'))
        ->assertSessionHas('success');

    expect(Hotel::count())->toBe(1);
    $hotel = Hotel::first();
    expect($hotel->title)->toBe('Hôtel Le Palais Royal');
    expect($hotel->slug)->toBe('hotel-le-palais-royal');
    expect($hotel->created_by)->toBe($this->user->id);
    expect($hotel->amenities()->count())->toBe(1);
});

test('a user can view their hotel dashboard', function () {
    Hotel::factory()->create([
        'created_by' => $this->user->id,
        'city_id' => $this->city->id,
    ]);

    $this->actingAs($this->user)
        ->get(route('hotel.dashboard'))
        ->assertOk()
        ->assertSee('Mes hôtels');
});

test('a user can view a hotel show page', function () {
    $hotel = Hotel::factory()->create([
        'created_by' => $this->user->id,
        'city_id' => $this->city->id,
        'is_verify' => true,
        'is_active' => true,
    ]);

    $hotel->images()->create([
        'image_url' => 'images/hotels/test.jpg',
    ]);

    $this->actingAs($this->user)
        ->get(route('hotel.show', $hotel))
        ->assertOk()
        ->assertSee($hotel->title);
});

test('hotel show page renders gallery with image switching like property', function () {
    $hotel = Hotel::factory()->create([
        'created_by' => $this->user->id,
        'city_id' => $this->city->id,
        'is_verify' => true,
        'is_active' => true,
    ]);

    $hotel->images()->create([
        'image_url' => 'images/hotels/test1.jpg',
    ]);
    $hotel->images()->create([
        'image_url' => 'images/hotels/test2.jpg',
    ]);
    $hotel->images()->create([
        'image_url' => 'images/hotels/test3.jpg',
    ]);

    $this->actingAs($this->user)
        ->get(route('hotel.show', $hotel))
        ->assertOk()
        ->assertSee('hotel-gallery')
        ->assertSee('hotel-main-img')
        ->assertSee('switchHotelImage')
        ->assertSee('g-thumb');
});

test('a user can update their hotel', function () {
    $hotel = Hotel::factory()->create([
        'created_by' => $this->user->id,
        'city_id' => $this->city->id,
    ]);

    $this->actingAs($this->user)
        ->put(route('hotel.update', $hotel), [
            'title' => 'Hôtel Le Royal Modifié',
            'description' => 'Description mise à jour de l\'hôtel.',
            'price_per_night' => 60000,
            'price_per_hour' => 6000,
            'star_rating' => 4,
            'rooms' => 12,
            'bathrooms' => 6,
            'furnished' => false,
            'address' => '15 Avenue de la Liberté',
            'city_id' => $this->city->id,
            'arrondissement_id' => $this->arrondissement->id,
            'amenities' => [$this->amenity->id],
            'kept_images' => [],
        ])
        ->assertRedirect(route('hotel.dashboard'))
        ->assertSessionHas('success');

    $hotel->refresh();
    expect($hotel->title)->toBe('Hôtel Le Royal Modifié');
    expect($hotel->price_per_night)->toBe(60000);
});

test('a user can delete their hotel', function () {
    $hotel = Hotel::factory()->create([
        'created_by' => $this->user->id,
        'city_id' => $this->city->id,
    ]);

    $this->actingAs($this->user)
        ->delete(route('hotel.destroy', $hotel))
        ->assertRedirect(route('hotel.index'))
        ->assertSessionHas('success');

    expect(Hotel::count())->toBe(0);
});

test('a user cannot update another user hotel', function () {
    $otherUser = User::factory()->create();
    $hotel = Hotel::factory()->create([
        'created_by' => $otherUser->id,
        'city_id' => $this->city->id,
    ]);

    $this->actingAs($this->user)
        ->put(route('hotel.update', $hotel), [
            'title' => 'Tentative de modification',
            'description' => 'Description.',
            'price_per_night' => 1000,
            'price_per_hour' => 100,
            'star_rating' => 1,
            'rooms' => 1,
            'bathrooms' => 1,
            'address' => 'Adresse de test',
            'city_id' => $this->city->id,
            'amenities' => [],
            'kept_images' => [],
        ])
        ->assertForbidden();
});

test('hotels index filters by city', function () {
    Hotel::factory()->create([
        'created_by' => $this->user->id,
        'city_id' => $this->city->id,
        'is_verify' => true,
        'is_active' => true,
    ]);

    $this->get(route('hotel.index', ['city_id' => $this->city->id]))
        ->assertOk()
        ->assertSee('Nos hôtels');
});

test('hotels index filters by star rating', function () {
    Hotel::factory()->create([
        'created_by' => $this->user->id,
        'city_id' => $this->city->id,
        'is_verify' => true,
        'is_active' => true,
        'star_rating' => 5,
    ]);

    $this->get(route('hotel.index', ['star_rating' => 5]))
        ->assertOk()
        ->assertSee('Nos hôtels');
});
