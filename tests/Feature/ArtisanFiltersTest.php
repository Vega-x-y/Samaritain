<?php

use App\Enums\ChantierStatus;
use App\Models\Artisan;
use App\Models\ArtisanRequest;
use App\Models\Chantier;
use App\Models\Property;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->artisan = Artisan::create([
        'user_id' => $this->user->id,
        'business_name' => 'Test Artisan Filters',
        'slug' => 'test-artisan-filters',
        'profession' => 'Plombier',
        'phone' => '+33123456789',
        'city' => 'Paris',
        'verified' => true,
        'is_active' => true,
    ]);
});

test('chantiers page renders combined statut and search filters and pills preserve the query', function () {
    Chantier::create([
        'artisan_id' => $this->artisan->id,
        'nom' => 'Cuisine sur mesure',
        'type' => 'menuiserie',
        'statut' => ChantierStatus::EN_COURS,
        'budget' => 1500,
    ]);

    $this->actingAs($this->user)
        ->get(route('artisan.chantiers.index', ['statut' => 'en_cours', 'search' => 'cuisine']))
        ->assertOk()
        ->assertSee('Cuisine sur mesure')
        // Les pastilles doivent conserver la recherche et les autres filtres
        ->assertSee('search=cuisine')
        ->assertSee('statut=en_cours');
});

test('requests page filters demands by statut', function () {
    $requester = User::factory()->create();

    ArtisanRequest::create([
        'artisan_id' => $this->artisan->id,
        'user_id' => $requester->id,
        'type' => 'devis',
        'message' => 'Demande en attente unique',
        'statut' => 'en_attente',
    ]);

    ArtisanRequest::create([
        'artisan_id' => $this->artisan->id,
        'user_id' => $requester->id,
        'type' => 'information',
        'message' => 'Demande acceptee unique',
        'statut' => 'acceptee',
    ]);

    $this->actingAs($this->user)
        ->get(route('artisan.requests', ['statut' => 'acceptee']))
        ->assertOk()
        ->assertSee('Demande acceptee unique')
        ->assertDontSee('Demande en attente unique');
});

test('documents page renders combined client filter and search', function () {
    $this->actingAs($this->user)
        ->get(route('artisan.documents.index', ['client_id' => 1, 'search' => 'devis']))
        ->assertOk()
        ->assertSee('search=devis');
});

test('finances aggregates respect the chantier_id filter', function () {
    $chantierA = Chantier::create([
        'artisan_id' => $this->artisan->id,
        'nom' => 'Chantier A',
        'type' => 'plomberie',
        'statut' => ChantierStatus::EN_COURS,
        'budget' => 1200,
    ]);

    Chantier::create([
        'artisan_id' => $this->artisan->id,
        'nom' => 'Chantier B',
        'type' => 'electricite',
        'statut' => ChantierStatus::ATTENTE,
        'budget' => 800,
    ]);

    // Sans filtre : les deux chantiers sont agrégés (1 200 + 800 = 2 000)
    $this->actingAs($this->user)
        ->get(route('artisan.finances.index'))
        ->assertOk()
        ->assertSee('2 000');

    // Avec filtre : seuls les agrégats du chantier A sont affichés
    $this->actingAs($this->user)
        ->get(route('artisan.finances.index', ['chantier_id' => $chantierA->id]))
        ->assertOk()
        ->assertSee('1 200')
        ->assertDontSee('2 000');
});

test('property search filters by rooms', function () {
    Property::factory()->create([
        'title' => 'Villa cinq pieces',
        'rooms' => 5,
        'is_active' => true,
        'is_verify' => true,
    ]);

    Property::factory()->create([
        'title' => 'Studio deux pieces',
        'rooms' => 2,
        'is_active' => true,
        'is_verify' => true,
    ]);

    $this->actingAs($this->user)
        ->get('/properties/search?rooms=5')
        ->assertOk()
        ->assertSee('Villa cinq pieces')
        ->assertDontSee('Studio deux pieces');
});

test('chantiers statut pills preserve the search parameter', function () {
    $this->actingAs($this->user)
        ->get(route('artisan.chantiers.index', ['search' => 'cuisine']))
        ->assertOk()
        // Le lien d'un filtre type doit contenir la recherche en cours
        ->assertSee('search=cuisine');
});
