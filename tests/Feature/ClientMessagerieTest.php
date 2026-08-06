<?php

use App\Models\Artisan;
use App\Models\Client;
use App\Models\Conversation;
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

test('client can view messagerie index', function () {
    $this->actingAs($this->user)
        ->get(route('client.messagerie.index'))
        ->assertOk()
        ->assertSee('Messagerie');
});

test('client can create a new conversation', function () {
    $this->actingAs($this->user)
        ->get(route('client.messagerie.create'))
        ->assertOk()
        ->assertSee('Sélectionner un artisan');
});

test('client can store a conversation', function () {
    $this->actingAs($this->user)
        ->post(route('client.messagerie.store'), [
            'artisan_id' => $this->artisan->id,
            'sujet' => 'Demande de devis',
        ])
        ->assertRedirect()
        ->assertSessionHas('success');

    $this->assertDatabaseHas('conversations', [
        'artisan_id' => $this->artisan->id,
        'client_id' => $this->client->id,
        'sujet' => 'Demande de devis',
    ]);
});

test('client can view a conversation', function () {
    $conversation = Conversation::create([
        'artisan_id' => $this->artisan->id,
        'client_id' => $this->client->id,
        'sujet' => 'Test',
    ]);

    $this->actingAs($this->user)
        ->get(route('client.messagerie.show', $conversation))
        ->assertOk()
        ->assertSee('Test');
});

test('client can send a text message', function () {
    $conversation = Conversation::create([
        'artisan_id' => $this->artisan->id,
        'client_id' => $this->client->id,
    ]);

    $this->actingAs($this->user)
        ->post(route('client.messagerie.message', $conversation), [
            'contenu' => 'Bonjour, je voudrais un devis.',
        ])
        ->assertRedirect()
        ->assertSessionHas('success');

    $this->assertDatabaseHas('messages', [
        'conversation_id' => $conversation->id,
        'expediteur_type' => 'client',
        'expediteur_id' => $this->client->id,
        'contenu' => 'Bonjour, je voudrais un devis.',
    ]);
});

test('client cannot access another client conversation', function () {
    $otherClient = Client::create([
        'artisan_id' => $this->artisan->id,
        'user_id' => User::factory()->create()->id,
        'nom' => 'Autre client',
        'telephone' => '+33699999999',
        'email' => 'autre@example.com',
        'type' => 'particulier',
    ]);

    $conversation = Conversation::create([
        'artisan_id' => $this->artisan->id,
        'client_id' => $otherClient->id,
    ]);

    $this->actingAs($this->user)
        ->get(route('client.messagerie.show', $conversation))
        ->assertForbidden();
});

test('client can delete a message from their conversation', function () {
    $conversation = Conversation::create([
        'artisan_id' => $this->artisan->id,
        'client_id' => $this->client->id,
    ]);

    $message = $conversation->messages()->create([
        'expediteur_type' => 'client',
        'expediteur_id' => $this->client->id,
        'contenu' => 'Message à supprimer',
        'lu' => true,
    ]);

    $this->actingAs($this->user)
        ->delete(route('client.messagerie.message.destroy', $message))
        ->assertRedirect()
        ->assertSessionHas('success');

    $this->assertDatabaseMissing('messages', ['id' => $message->id]);
});

test('client cannot delete a message from another client conversation', function () {
    $otherClient = Client::create([
        'artisan_id' => $this->artisan->id,
        'user_id' => User::factory()->create()->id,
        'nom' => 'Autre client',
        'telephone' => '+33699999999',
        'email' => 'autre@example.com',
        'type' => 'particulier',
    ]);

    $conversation = Conversation::create([
        'artisan_id' => $this->artisan->id,
        'client_id' => $otherClient->id,
    ]);

    $message = $conversation->messages()->create([
        'expediteur_type' => 'client',
        'expediteur_id' => $otherClient->id,
        'contenu' => 'Message à supprimer',
        'lu' => true,
    ]);

    $this->actingAs($this->user)
        ->delete(route('client.messagerie.message.destroy', $message))
        ->assertForbidden();

    $this->assertDatabaseHas('messages', ['id' => $message->id]);
});

test('client can delete a conversation', function () {
    $conversation = Conversation::create([
        'artisan_id' => $this->artisan->id,
        'client_id' => $this->client->id,
    ]);

    $conversation->messages()->create([
        'expediteur_type' => 'client',
        'expediteur_id' => $this->client->id,
        'contenu' => 'Test message',
        'lu' => true,
    ]);

    $this->actingAs($this->user)
        ->delete(route('client.messagerie.destroy', $conversation))
        ->assertRedirect()
        ->assertSessionHas('success');

    $this->assertDatabaseMissing('conversations', ['id' => $conversation->id]);
    $this->assertDatabaseMissing('messages', ['conversation_id' => $conversation->id]);
});

test('client can delete all conversations', function () {
    $conversation1 = Conversation::create([
        'artisan_id' => $this->artisan->id,
        'client_id' => $this->client->id,
    ]);

    $conversation2 = Conversation::create([
        'artisan_id' => $this->artisan->id,
        'client_id' => $this->client->id,
    ]);

    $conversation1->messages()->create([
        'expediteur_type' => 'client',
        'expediteur_id' => $this->client->id,
        'contenu' => 'Message 1',
        'lu' => true,
    ]);

    $conversation2->messages()->create([
        'expediteur_type' => 'client',
        'expediteur_id' => $this->client->id,
        'contenu' => 'Message 2',
        'lu' => true,
    ]);

    $this->actingAs($this->user)
        ->delete(route('client.messagerie.destroy-all'))
        ->assertRedirect()
        ->assertSessionHas('success');

    $this->assertDatabaseMissing('conversations', ['id' => $conversation1->id]);
    $this->assertDatabaseMissing('conversations', ['id' => $conversation2->id]);
});
