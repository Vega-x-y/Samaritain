
<?php

use App\Enums\ChantierStatus;
use App\Models\Artisan;
use App\Models\Chantier;
use App\Models\Client;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->artisan = Artisan::create([
        'user_id' => User::factory()->create()->id,
        'business_name' => 'Test Artisan',
        'slug' => 'test-artisan',
        'profession' => 'Plombier',
        'phone' => '+33123456789',
        'city' => 'Paris',
        'verified' => true,
        'is_active' => true,
    ]);
    $this->client = Client::create([
        'artisan_id' => $this->artisan->id,
        'user_id' => $this->user->id,
        'nom' => 'Dupont Jean',
        'telephone' => '+33612345678',
        'email' => 'jean@example.com',
        'type' => 'particulier',
    ]);
});

test('client dashboard loads successfully', function () {
    $this->actingAs($this->user)
        ->get(route('client.dashboard'))
        ->assertOk()
        ->assertSee('Mon espace client');
});

test('client dashboard shows chantiers linked to user', function () {
    Chantier::create([
        'artisan_id' => $this->artisan->id,
        'client_id' => $this->user->id,
        'nom' => 'Rénovation cuisine',
        'type' => 'plomberie',
        'statut' => ChantierStatus::EN_COURS,
    ]);

    $this->actingAs($this->user)
        ->get(route('client.dashboard'))
        ->assertOk()
        ->assertSee('Rénovation cuisine');
});

test('client chantiers index page loads successfully', function () {
    Chantier::create([
        'artisan_id' => $this->artisan->id,
        'client_id' => $this->user->id,
        'nom' => 'Rénovation salle de bain',
        'type' => 'plomberie',
        'statut' => ChantierStatus::EN_COURS,
    ]);

    $this->actingAs($this->user)
        ->get(route('client.chantiers.index'))
        ->assertOk()
        ->assertSee('Rénovation salle de bain')
        ->assertSee('Mes chantiers');
});

test('client chantiers index filters by statut', function () {
    Chantier::create([
        'artisan_id' => $this->artisan->id,
        'client_id' => $this->user->id,
        'nom' => 'Chantier en cours',
        'type' => 'plomberie',
        'statut' => ChantierStatus::EN_COURS,
    ]);

    Chantier::create([
        'artisan_id' => $this->artisan->id,
        'client_id' => $this->user->id,
        'nom' => 'Chantier terminé',
        'type' => 'electricite',
        'statut' => ChantierStatus::TERMINE,
    ]);

    $this->actingAs($this->user)
        ->get(route('client.chantiers.index', ['statut' => 'termine']))
        ->assertOk()
        ->assertSee('Chantier terminé')
        ->assertDontSee('Chantier en cours');
});

test('client dashboard shows empty state when no chantiers', function () {
    $this->actingAs($this->user)
        ->get(route('client.dashboard'))
        ->assertOk()
        ->assertSee('Aucun chantier pour le moment.');
});

test('client dashboard is accessible to any authenticated user', function () {
    $userWithoutClient = User::factory()->create();

    $this->actingAs($userWithoutClient)
        ->get(route('client.dashboard'))
        ->assertOk();
});

test('user has clients relationship', function () {
    expect($this->user->clients)->toHaveCount(1);
    expect($this->user->clients->first()->id)->toBe($this->client->id);
});
