<?php

use App\Models\Artisan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->artisan = Artisan::create([
        'user_id' => $this->user->id,
        'business_name' => 'Test Artisan Search',
        'slug' => 'test-artisan-search',
        'profession' => 'Plombier',
        'phone' => '+33123456789',
        'city' => 'Paris',
        'verified' => true,
        'is_active' => true,
    ]);
});

test('search bar is accessible on artisan clients page', function () {
    $this->actingAs($this->user)
        ->get(route('artisan.clients.index', ['search' => 'Jean']))
        ->assertOk()
        ->assertSee('Rechercher un client');
});

test('search bar is accessible on artisan stock page', function () {
    $this->actingAs($this->user)
        ->get(route('artisan.stock.index', ['search' => 'bois']))
        ->assertOk()
        ->assertSee('Rechercher un article');
});

test('search bar is accessible on artisan chantiers page', function () {
    $this->actingAs($this->user)
        ->get(route('artisan.chantiers.index', ['search' => 'cuisine']))
        ->assertOk()
        ->assertSee('Rechercher un chantier');
});

test('search bar is accessible on artisan equipe page', function () {
    $this->actingAs($this->user)
        ->get(route('artisan.equipe.index', ['search' => 'ouvrier']))
        ->assertOk()
        ->assertSee('Rechercher un membre');
});

test('search bar is accessible on artisan documents page', function () {
    $this->actingAs($this->user)
        ->get(route('artisan.documents.index', ['search' => 'devis']))
        ->assertOk()
        ->assertSee('Rechercher un document');
});

test('search bar is accessible on artisan messagerie page', function () {
    $this->actingAs($this->user)
        ->get(route('artisan.messagerie.index', ['search' => 'bonjour']))
        ->assertOk()
        ->assertSee('Rechercher un message');
});

test('search bar is accessible on artisan planning page', function () {
    $this->actingAs($this->user)
        ->get(route('artisan.planning.index', ['search' => 'intervention']))
        ->assertOk()
        ->assertSee('Rechercher un événement');
});

test('search bar is accessible on artisan finances page', function () {
    $this->actingAs($this->user)
        ->get(route('artisan.finances.index', ['search' => 'acompte']))
        ->assertOk()
        ->assertSee('Rechercher une transaction');
});

test('search bar is accessible on artisan projects page', function () {
    $this->actingAs($this->user)
        ->get(route('artisan.projects.index', $this->artisan, ['search' => 'terrasse']))
        ->assertOk()
        ->assertSee('Rechercher un projet');
});

test('search bar is accessible on artisan requests page', function () {
    $this->actingAs($this->user)
        ->get(route('artisan.requests', ['search' => 'devis']))
        ->assertOk()
        ->assertSee('Rechercher une demande');
});

test('search bar is accessible on artisan reviews page', function () {
    $this->actingAs($this->user)
        ->get(route('artisan.reviews', ['search' => 'super']))
        ->assertOk()
        ->assertSee('Rechercher un avis');
});
