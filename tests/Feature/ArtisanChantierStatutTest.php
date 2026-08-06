<?php

use App\Enums\ChantierStatus;
use App\Models\Artisan;
use App\Models\Chantier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->artisan = Artisan::create([
        'user_id' => $this->user->id,
        'business_name' => 'Test Artisan',
        'slug' => 'test-artisan',
        'profession' => 'Plombier',
        'phone' => '+33123456789',
        'city' => 'Paris',
        'verified' => true,
        'is_active' => true,
    ]);

    $this->chantier = Chantier::create([
        'artisan_id' => $this->artisan->id,
        'nom' => 'Rénovation salle de bain',
        'type' => 'plomberie',
        'statut' => ChantierStatus::EN_COURS,
    ]);
});

test('artisan can change chantier status to termine', function () {
    $this->actingAs($this->user)
        ->patch(route('artisan.chantiers.statut', $this->chantier), [
            'statut' => 'termine',
        ])
        ->assertRedirect(route('artisan.chantiers.index'))
        ->assertSessionHas('success');

    $this->chantier->refresh();
    expect($this->chantier->statut)->toBe(ChantierStatus::TERMINE);
});

test('artisan can change chantier status to en_cours', function () {
    $this->chantier->update(['statut' => ChantierStatus::ATTENTE]);

    $this->actingAs($this->user)
        ->patch(route('artisan.chantiers.statut', $this->chantier), [
            'statut' => 'en_cours',
        ])
        ->assertRedirect(route('artisan.chantiers.index'))
        ->assertSessionHas('success');

    $this->chantier->refresh();
    expect($this->chantier->statut)->toBe(ChantierStatus::EN_COURS);
});

test('artisan can change chantier status to arret', function () {
    $this->actingAs($this->user)
        ->patch(route('artisan.chantiers.statut', $this->chantier), [
            'statut' => 'arret',
        ])
        ->assertRedirect(route('artisan.chantiers.index'))
        ->assertSessionHas('success');

    $this->chantier->refresh();
    expect($this->chantier->statut)->toBe(ChantierStatus::ARRET);
});

test('artisan can change chantier status to attente', function () {
    $this->actingAs($this->user)
        ->patch(route('artisan.chantiers.statut', $this->chantier), [
            'statut' => 'attente',
        ])
        ->assertRedirect(route('artisan.chantiers.index'))
        ->assertSessionHas('success');

    $this->chantier->refresh();
    expect($this->chantier->statut)->toBe(ChantierStatus::ATTENTE);
});

test('invalid chantier status is rejected', function () {
    $this->actingAs($this->user)
        ->patch(route('artisan.chantiers.statut', $this->chantier), [
            'statut' => 'invalide',
        ])
        ->assertSessionHasErrors('statut');

    $this->chantier->refresh();
    expect($this->chantier->statut)->toBe(ChantierStatus::EN_COURS);
});

test('artisan cannot change status of another artisan chantier', function () {
    $otherUser = User::factory()->create();
    $otherArtisan = Artisan::create([
        'user_id' => $otherUser->id,
        'business_name' => 'Autre Artisan',
        'slug' => 'autre-artisan',
        'profession' => 'Électricien',
        'phone' => '+33111111111',
        'city' => 'Lyon',
        'verified' => true,
        'is_active' => true,
    ]);

    $otherChantier = Chantier::create([
        'artisan_id' => $otherArtisan->id,
        'nom' => 'Chantier autre artisan',
        'type' => 'electricite',
        'statut' => ChantierStatus::EN_COURS,
    ]);

    $this->actingAs($this->user)
        ->patch(route('artisan.chantiers.statut', $otherChantier), [
            'statut' => 'termine',
        ])
        ->assertForbidden();

    $otherChantier->refresh();
    expect($otherChantier->statut)->toBe(ChantierStatus::EN_COURS);
});

test('dashboard reflects chantier status change', function () {
    $this->actingAs($this->user)
        ->patch(route('artisan.chantiers.statut', $this->chantier), [
            'statut' => 'termine',
        ])
        ->assertRedirect(route('artisan.chantiers.index'));

    $this->actingAs($this->user)
        ->get(route('artisan.dashboard'))
        ->assertOk();
});
