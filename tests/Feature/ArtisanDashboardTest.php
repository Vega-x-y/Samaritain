<?php

use App\Enums\ChantierStatus;
use App\Models\Artisan;
use App\Models\Chantier;
use App\Models\Facture;
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
});

test('artisan dashboard loads successfully with factures table', function () {
    $chantier = Chantier::create([
        'artisan_id' => $this->artisan->id,
        'nom' => 'Rénovation salle de bain',
        'type' => 'plomberie',
        'statut' => ChantierStatus::EN_COURS,
    ]);

    Facture::create([
        'chantier_id' => $chantier->id,
        'numero' => 'F-001',
        'montant_ht' => 1000.00,
        'montant_ttc' => 1200.00,
        'date_emission' => now(),
        'statut' => 'payee',
    ]);

    $this->actingAs($this->user)
        ->get(route('artisan.dashboard'))
        ->assertOk();
});

test('artisan dashboard loads successfully without factures', function () {
    $this->actingAs($this->user)
        ->get(route('artisan.dashboard'))
        ->assertOk();
});

test('artisan dashboard is forbidden when user has no artisan profile', function () {
    $userWithoutArtisan = User::factory()->create();

    $this->actingAs($userWithoutArtisan)
        ->get(route('artisan.dashboard'))
        ->assertForbidden();
});
