<?php

use App\Enums\ChantierStatus;
use App\Models\Artisan;
use App\Models\Chantier;
use App\Models\ChantierTransaction;
use App\Models\Depense;
use App\Models\Facture;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->artisan = Artisan::create([
        'user_id' => $this->user->id,
        'business_name' => 'Test Artisan Finances',
        'slug' => 'test-artisan-finances',
        'profession' => 'Plombier',
        'phone' => '+33123456789',
        'city' => 'Paris',
        'verified' => true,
        'is_active' => true,
    ]);
});

test('finances dashboard renders the kpi cards, header and actions', function () {
    $this->actingAs($this->user)
        ->get(route('artisan.finances.index'))
        ->assertOk()
        ->assertSee('Centre financier')
        ->assertSee('CA Total')
        ->assertSee('Dépenses totales')
        ->assertSee('Bénéfice net')
        ->assertSee('Marge')
        ->assertSee('Acomptes reçus')
        ->assertSee('Impayés')
        ->assertSee('Répartition des revenus')
        ->assertSee('Dépenses par type')
        ->assertSee('Exporter le bilan')
        ->assertSee('Nouveau projet')
        ->assertSee(route('notifications.api'))
        ->assertSee(route('artisan.chantiers.create'));
});

test('finances dashboard is forbidden for users without artisan profile', function () {
    $userWithoutArtisan = User::factory()->create();

    $this->actingAs($userWithoutArtisan)
        ->get(route('artisan.finances.index'))
        ->assertForbidden();
});

test('finances dashboard computes kpi aggregates and links a chantier to its detail', function () {
    $chantier = Chantier::create([
        'artisan_id' => $this->artisan->id,
        'nom' => 'Rénovation salle de bain',
        'type' => 'plomberie',
        'statut' => ChantierStatus::EN_COURS,
        'budget' => 1200.00,
    ]);

    // CA payé → total_ca = 1 200
    Facture::create([
        'chantier_id' => $chantier->id,
        'numero' => 'F-001',
        'montant_ht' => 1000.00,
        'montant_ttc' => 1200.00,
        'date_emission' => now(),
        'statut' => 'payee',
    ]);

    // Impayés = CA payé (1 200) − acomptes reçus (200) = 1 000
    Facture::create([
        'chantier_id' => $chantier->id,
        'numero' => 'F-002',
        'montant_ht' => 2500.00,
        'montant_ttc' => 3000.00,
        'date_emission' => now(),
        'date_echeance' => now()->addDays(30),
        'statut' => 'envoyee',
    ]);

    // Dépense → total_depenses = 500 ; bénéfice net = 1 200 - 500 = 700
    Depense::create([
        'chantier_id' => $chantier->id,
        'categorie' => 'materiaux',
        'montant' => 500.00,
        'date' => now(),
        'description' => 'Matériaux plomberie',
    ]);

    // Acompte reçu → acomptesRecus = 200
    ChantierTransaction::create([
        'chantier_id' => $chantier->id,
        'type' => 'acompte',
        'montant' => 200.00,
        'statut' => 'recu',
        'date' => now(),
    ]);

    $this->actingAs($this->user)
        ->get(route('artisan.finances.index'))
        ->assertOk()
        ->assertSee('1 200')
        ->assertSee('500')
        ->assertSee('700')
        ->assertSee('1 000')
        ->assertSee('58')
        ->assertSee('Matériaux')
        ->assertSee('Répartition des revenus')
        ->assertSee('Dépenses par type')
        ->assertSee(route('artisan.finances.show', $chantier))
        ->assertSee(route('artisan.chantiers.create'));
});
