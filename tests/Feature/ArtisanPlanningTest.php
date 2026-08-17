<?php

use App\Enums\EvenementType;
use App\Models\Artisan;
use App\Models\Evenement;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->artisan = Artisan::create([
        'user_id' => $this->user->id,
        'business_name' => 'Test Artisan Planning',
        'slug' => 'test-artisan-planning',
        'profession' => 'Électricien',
        'phone' => '+33123456789',
        'city' => 'Lyon',
        'verified' => true,
        'is_active' => true,
    ]);
});

test('artisan planning index loads successfully without events', function () {
    $this->actingAs($this->user)
        ->get(route('artisan.planning.index'))
        ->assertOk()
        ->assertSee('Planning');
});

test('artisan planning index displays events list with actions', function () {
    Evenement::factory()->create([
        'artisan_id' => $this->artisan->id,
        'titre' => 'Intervention test',
        'type' => EvenementType::INTERVENTION,
    ]);

    $this->actingAs($this->user)
        ->get(route('artisan.planning.index'))
        ->assertOk()
        ->assertSee('Intervention test')
        ->assertSee('Mes événements');
});

test('artisan planning index shows calendar markers for event dates', function () {
    $evenement = Evenement::factory()->create([
        'artisan_id' => $this->artisan->id,
        'titre' => 'Réunion chantier',
        'date_debut' => now()->addDays(2)->setHour(10)->setMinute(0),
        'date_fin' => now()->addDays(2)->setHour(11)->setMinute(0),
    ]);

    $this->actingAs($this->user)
        ->get(route('artisan.planning.index'))
        ->assertOk()
        ->assertSee('animate-pulse');
});

test('artisan can create an event', function () {
    $this->actingAs($this->user)
        ->post(route('artisan.planning.store'), [
            'titre' => 'Nouvelle intervention',
            'chantier_id' => null,
            'date_debut' => now()->addDay()->format('Y-m-d\TH:i'),
            'date_fin' => now()->addDay()->addHour()->format('Y-m-d\TH:i'),
            'type' => 'intervention',
            'description' => 'Description test',
        ])
        ->assertRedirect(route('artisan.planning.index'));

    $this->assertDatabaseHas('evenements', [
        'artisan_id' => $this->artisan->id,
        'titre' => 'Nouvelle intervention',
    ]);
});

test('artisan can view an event', function () {
    $evenement = Evenement::factory()->create([
        'artisan_id' => $this->artisan->id,
        'titre' => 'Événement à voir',
    ]);

    $this->actingAs($this->user)
        ->get(route('artisan.planning.show', $evenement))
        ->assertOk()
        ->assertSee('Événement à voir');
});

test('artisan can delete an event', function () {
    $evenement = Evenement::factory()->create([
        'artisan_id' => $this->artisan->id,
    ]);

    $this->actingAs($this->user)
        ->delete(route('artisan.planning.destroy', $evenement))
        ->assertRedirect(route('artisan.planning.index'));

    $this->assertDatabaseMissing('evenements', [
        'id' => $evenement->id,
    ]);
});

test('artisan cannot view event from another artisan', function () {
    $otherUser = User::factory()->create();
    $otherArtisan = Artisan::create([
        'user_id' => $otherUser->id,
        'business_name' => 'Other Artisan',
        'slug' => 'other-artisan',
        'profession' => 'Plombier',
        'phone' => '+33123456790',
        'city' => 'Paris',
        'verified' => true,
        'is_active' => true,
    ]);

    $evenement = Evenement::factory()->create([
        'artisan_id' => $otherArtisan->id,
    ]);

    $this->actingAs($this->user)
        ->get(route('artisan.planning.show', $evenement))
        ->assertForbidden();
});
