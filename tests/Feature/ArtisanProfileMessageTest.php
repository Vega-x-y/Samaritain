<?php

use App\Enums\ClientType;
use App\Models\Artisan;
use App\Models\Client;
use App\Models\Conversation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

const TEST_CSRF_TOKEN = 'unit-test-token';

beforeEach(function () {
    // Le middleware CSRF n'est pas contourné dans cet environnement de test (Windows),
    // on renseigne donc un jeton explicite pour les requêtes POST.
    $this->withSession(['_token' => TEST_CSRF_TOKEN]);

    $this->viewer = User::factory()->create();
    $this->owner = User::factory()->create();

    $this->artisan = Artisan::create([
        'user_id' => $this->owner->id,
        'business_name' => 'Test Artisan',
        'slug' => 'test-artisan',
        'profession' => 'Plombier',
        'phone' => '+33123456789',
        'city' => 'Paris',
        'verified' => true,
        'is_active' => true,
    ]);
});

test('a connected visitor can send a message to the artisan from his profile', function () {
    $this->actingAs($this->viewer)
        ->post(route('artisans.message.store', $this->artisan), [
            '_token' => TEST_CSRF_TOKEN,
            'contenu' => 'Bonjour, je souhaite un devis.',
        ])
        ->assertRedirect()
        ->assertSessionHas('success');

    // Un Client est créé pour l'utilisateur, lié à CET artisan
    $this->assertDatabaseHas('clients', [
        'user_id' => $this->viewer->id,
        'artisan_id' => $this->artisan->id,
        'nom' => $this->viewer->name,
        'type' => ClientType::PARTICULIER->value,
    ]);

    $client = Client::where('user_id', $this->viewer->id)->where('artisan_id', $this->artisan->id)->first();

    $this->assertDatabaseHas('conversations', [
        'artisan_id' => $this->artisan->id,
        'client_id' => $client->id,
    ]);

    $conversationId = Conversation::where('artisan_id', $this->artisan->id)
        ->where('client_id', $client->id)
        ->value('id');

    $this->assertDatabaseHas('messages', [
        'conversation_id' => $conversationId,
        'expediteur_type' => 'client',
        'expediteur_id' => $client->id,
        'contenu' => 'Bonjour, je souhaite un devis.',
    ]);
});

test('a connected visitor can attach a file to a message', function () {
    Storage::fake('r2');

    $this->actingAs($this->viewer)
        ->post(route('artisans.message.store', $this->artisan), [
            '_token' => TEST_CSRF_TOKEN,
            'fichier' => UploadedFile::fake()->create('devis.pdf', 100, 'application/pdf'),
        ])
        ->assertRedirect()
        ->assertSessionHas('success');

    $message = DB::table('messages')->first();

    $this->assertNotNull($message);
    $this->assertNotNull($message->fichier_path);
    $this->assertSame('devis.pdf', $message->fichier_nom);
});

test('messages sent to different artisans stay in separate conversations', function () {
    $artisan2 = Artisan::create([
        'user_id' => $this->owner->id,
        'business_name' => 'Autre Artisan',
        'slug' => 'autre-artisan',
        'profession' => 'Électricien',
        'phone' => '+33123456789',
        'city' => 'Lyon',
        'verified' => true,
        'is_active' => true,
    ]);

    $this->actingAs($this->viewer)
        ->post(route('artisans.message.store', $this->artisan), ['_token' => TEST_CSRF_TOKEN, 'contenu' => 'Message 1'])
        ->assertRedirect();
    $this->actingAs($this->viewer)
        ->post(route('artisans.message.store', $artisan2), ['_token' => TEST_CSRF_TOKEN, 'contenu' => 'Message 2'])
        ->assertRedirect();

    $this->assertSame(2, Client::where('user_id', $this->viewer->id)->count());
    $this->assertSame(1, Conversation::where('artisan_id', $this->artisan->id)->count());
    $this->assertSame(1, Conversation::where('artisan_id', $artisan2->id)->count());
});

test('the artisan owner cannot send a message to his own profile', function () {
    $this->actingAs($this->owner)
        ->post(route('artisans.message.store', $this->artisan), [
            '_token' => TEST_CSRF_TOKEN,
            'contenu' => 'Salut moi-même',
        ])
        ->assertForbidden();

    $this->assertDatabaseMissing('conversations', ['artisan_id' => $this->artisan->id]);
});

test('a guest is redirected to login when trying to message an artisan', function () {
    $this->post(route('artisans.message.store', $this->artisan), [
        '_token' => TEST_CSRF_TOKEN,
        'contenu' => 'test',
    ])->assertRedirect(route('login'));
});

test('the artisan show page renders the messaging widget for a connected visitor', function () {
    $client = Client::create([
        'artisan_id' => $this->artisan->id,
        'user_id' => $this->viewer->id,
        'nom' => $this->viewer->name,
        'telephone' => '+33612345678',
        'type' => ClientType::PARTICULIER->value,
    ]);
    $conversation = Conversation::create([
        'artisan_id' => $this->artisan->id,
        'client_id' => $client->id,
    ]);
    $conversation->messages()->create([
        'expediteur_type' => 'client',
        'expediteur_id' => $client->id,
        'contenu' => 'Premier message',
        'lu' => true,
    ]);

    $this->actingAs($this->viewer)
        ->get(route('artisans.show', $this->artisan))
        ->assertOk()
        ->assertSee('Messagerie')
        ->assertSee('Premier message');
});

test('a guest sees the Messagerie tab with a login prompt to start a discussion', function () {
    $this->get(route('artisans.show', $this->artisan))
        ->assertOk()
        // Le bouton d'onglet Messagerie est visible pour tout le monde
        ->assertSee('Messagerie')
        // L'alerte demandant de se connecter pour discuter est affichée
        ->assertSee('Connectez-vous')
        ->assertSee('pour commencer une discussion avec Test Artisan')
        // Le formulaire d'envoi n'est pas affiché pour un invité
        ->assertDontSee('Écrivez votre message');
});

test('the artisan show page hides the messaging widget for its owner', function () {
    $this->actingAs($this->owner)
        ->get(route('artisans.show', $this->artisan))
        ->assertOk()
        ->assertDontSee('Échangez directement');
});

test('the Messagerie tab shows an unread badge when a new message is received', function () {
    $client = Client::create([
        'artisan_id' => $this->artisan->id,
        'user_id' => $this->viewer->id,
        'nom' => $this->viewer->name,
        'telephone' => '+33612345678',
        'type' => ClientType::PARTICULIER->value,
    ]);
    $conversation = Conversation::create([
        'artisan_id' => $this->artisan->id,
        'client_id' => $client->id,
    ]);
    $conversation->messages()->create([
        'expediteur_type' => 'artisan',
        'expediteur_id' => $this->artisan->id,
        'contenu' => 'Nouveau message non lu',
        'lu' => false,
    ]);

    $response = $this->actingAs($this->viewer)
        ->get(route('artisans.show', $this->artisan))
        ->assertOk()
        ->assertSee('Nouveau message non lu');

    // La pastille colorée de notification (messages non lus) est rendue
    $response->assertSee('rounded-full bg-primary text-primary-foreground', false);

    // La visite de la page marque le message comme lu
    $this->assertDatabaseHas('messages', [
        'contenu' => 'Nouveau message non lu',
        'lu' => true,
    ]);
});

test('the thread endpoint only serves the user own conversation', function () {
    $client = Client::create([
        'artisan_id' => $this->artisan->id,
        'user_id' => $this->viewer->id,
        'nom' => $this->viewer->name,
        'telephone' => '+33612345678',
        'type' => ClientType::PARTICULIER->value,
    ]);
    $conversation = Conversation::create([
        'artisan_id' => $this->artisan->id,
        'client_id' => $client->id,
    ]);
    $conversation->messages()->create([
        'expediteur_type' => 'artisan',
        'expediteur_id' => $this->artisan->id,
        'contenu' => 'Réponse artisan',
        'lu' => false,
    ]);

    $this->actingAs($this->viewer)
        ->get(route('artisans.message.index', $this->artisan))
        ->assertOk()
        ->assertSee('Réponse artisan');
});
