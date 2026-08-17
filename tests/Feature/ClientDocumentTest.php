<?php

use App\Models\Artisan;
use App\Models\Client;
use App\Models\Document;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(function () {
    Storage::fake('r2');

    // Artisan user
    $this->artisanUser = User::factory()->create();
    $this->artisan = Artisan::create([
        'user_id' => $this->artisanUser->id,
        'business_name' => 'Test Artisan',
        'slug' => 'test-artisan',
        'profession' => 'Plombier',
        'phone' => '+33123456789',
        'city' => 'Paris',
        'verified' => true,
        'is_active' => true,
    ]);

    // Client user
    $this->clientUser = User::factory()->create();
    $this->client = Client::create([
        'artisan_id' => $this->artisan->id,
        'user_id' => $this->clientUser->id,
        'nom' => 'Dupont Jean',
        'telephone' => '+33612345678',
        'email' => 'jean@example.com',
        'type' => 'particulier',
    ]);
});

test('artisan can send a devis to a client and status changes to sent', function () {
    $document = Document::create([
        'client_id' => $this->client->id,
        'nom' => 'Devis rénovation',
        'path' => 'documents/devis.pdf',
        'type' => 'devis',
        'mime_type' => 'application/pdf',
        'size' => 1000,
        'status' => Document::STATUS_DRAFT,
    ]);

    $this->actingAs($this->artisanUser)
        ->post(route('artisan.documents.send-to-client', $document), [
            'client_id' => $this->client->id,
        ])
        ->assertRedirect()
        ->assertSessionHas('success');

    $document->refresh();
    expect($document->status)->toBe(Document::STATUS_SENT);
});

test('client can view a sent devis to accept', function () {
    $document = Document::create([
        'client_id' => $this->client->id,
        'nom' => 'Devis rénovation',
        'path' => 'documents/devis.pdf',
        'type' => 'devis',
        'mime_type' => 'application/pdf',
        'size' => 1000,
        'status' => Document::STATUS_SENT,
    ]);

    $this->actingAs($this->clientUser)
        ->get(route('client.documents.show', $document))
        ->assertOk()
        ->assertSee('Acceptation du devis')
        ->assertSee('attestation')
        ->assertSee('Renvoyer')
        ->assertDontSee('Signature électronique')
        ->assertDontSee('Valider la signature');
});

test('client can return a devis with attestation', function () {
    $document = Document::create([
        'client_id' => $this->client->id,
        'nom' => 'Devis rénovation',
        'path' => 'documents/devis.pdf',
        'type' => 'devis',
        'mime_type' => 'application/pdf',
        'size' => 1000,
        'status' => Document::STATUS_SENT,
    ]);

    $this->actingAs($this->clientUser)
        ->post(route('client.documents.return', $document), [
            'attestation' => '1',
        ])
        ->assertRedirect()
        ->assertSessionHas('success');

    $document->refresh();
    expect($document->status)->toBe(Document::STATUS_SIGNED);
    expect($document->signed_at)->not->toBeNull();
    expect($document->signature_data)->not->toBeNull();
    expect($document->signature_data['signed_by_user_id'])->toBe($this->clientUser->id);
    expect($document->signature_data['signed_by_client_id'])->toBe($this->client->id);
    expect($document->signature_data['returned_without_signature'])->toBeTrue();
});

test('client cannot return a devis without attestation checkbox', function () {
    $document = Document::create([
        'client_id' => $this->client->id,
        'nom' => 'Devis rénovation',
        'path' => 'documents/devis.pdf',
        'type' => 'devis',
        'mime_type' => 'application/pdf',
        'size' => 1000,
        'status' => Document::STATUS_SENT,
    ]);

    $this->actingAs($this->clientUser)
        ->post(route('client.documents.return', $document))
        ->assertSessionHasErrors('attestation');

    $document->refresh();
    expect($document->status)->toBe(Document::STATUS_SENT);
});

test('artisan cannot export an unsigned devis', function () {
    $document = Document::create([
        'client_id' => $this->client->id,
        'nom' => 'Devis rénovation',
        'path' => 'documents/devis.pdf',
        'type' => 'devis',
        'mime_type' => 'application/pdf',
        'size' => 1000,
        'status' => Document::STATUS_SENT,
    ]);

    $this->actingAs($this->artisanUser)
        ->get(route('artisan.documents.export-pdf', $document))
        ->assertRedirect()
        ->assertSessionHas('error');
});

test('artisan can export a returned devis without signature', function () {
    Storage::disk('r2')->put('documents/devis.pdf', 'fake pdf content');

    $document = Document::create([
        'client_id' => $this->client->id,
        'nom' => 'Devis rénovation',
        'path' => 'documents/devis.pdf',
        'type' => 'devis',
        'mime_type' => 'application/pdf',
        'size' => 1000,
        'status' => Document::STATUS_SIGNED,
        'signed_at' => now(),
        'signature_data' => [
            'signature' => null,
            'signed_by_user_id' => $this->clientUser->id,
            'signed_by_client_id' => $this->client->id,
            'signed_at' => now()->toIso8601String(),
            'returned_without_signature' => true,
        ],
    ]);

    $this->actingAs($this->artisanUser)
        ->get(route('artisan.documents.export-pdf', $document))
        ->assertOk();
});

test('artisan can export a non-devis document without signing', function () {
    Storage::disk('r2')->put('documents/facture.pdf', 'fake pdf content');

    $document = Document::create([
        'client_id' => $this->client->id,
        'nom' => 'Facture',
        'path' => 'documents/facture.pdf',
        'type' => 'facture',
        'mime_type' => 'application/pdf',
        'size' => 1000,
        'status' => Document::STATUS_DRAFT,
    ]);

    $this->actingAs($this->artisanUser)
        ->get(route('artisan.documents.export-pdf', $document))
        ->assertOk();
});

test('client cannot access another client devis', function () {
    $otherClient = Client::create([
        'artisan_id' => $this->artisan->id,
        'user_id' => User::factory()->create()->id,
        'nom' => 'Martin Sophie',
        'telephone' => '+33611111111',
        'email' => 'sophie@example.com',
        'type' => 'particulier',
    ]);

    $document = Document::create([
        'client_id' => $otherClient->id,
        'nom' => 'Devis autre client',
        'path' => 'documents/devis.pdf',
        'type' => 'devis',
        'mime_type' => 'application/pdf',
        'size' => 1000,
        'status' => Document::STATUS_SENT,
    ]);

    $this->actingAs($this->clientUser)
        ->get(route('client.documents.show', $document))
        ->assertForbidden();
});

test('client cannot return a devis that belongs to another client', function () {
    $otherClient = Client::create([
        'artisan_id' => $this->artisan->id,
        'user_id' => User::factory()->create()->id,
        'nom' => 'Martin Sophie',
        'telephone' => '+33611111111',
        'email' => 'sophie@example.com',
        'type' => 'particulier',
    ]);

    $document = Document::create([
        'client_id' => $otherClient->id,
        'nom' => 'Devis autre client',
        'path' => 'documents/devis.pdf',
        'type' => 'devis',
        'mime_type' => 'application/pdf',
        'size' => 1000,
        'status' => Document::STATUS_SENT,
    ]);

    $this->actingAs($this->clientUser)
        ->post(route('client.documents.return', $document), [
            'attestation' => '1',
        ])
        ->assertForbidden();
});

test('client can view a facture document with download button', function () {
    Storage::disk('r2')->put('documents/facture.pdf', 'fake pdf content');

    $document = Document::create([
        'client_id' => $this->client->id,
        'nom' => 'Facture travaux',
        'path' => 'documents/facture.pdf',
        'type' => 'facture',
        'mime_type' => 'application/pdf',
        'size' => 1000,
        'status' => Document::STATUS_SENT,
        'metadata' => [
            'numero' => 'FAC-2026-001',
            'date_emission' => '2026-08-03',
            'montant_ht' => 50000,
            'tva' => 18,
            'montant_ttc' => 59000,
        ],
    ]);

    $this->actingAs($this->clientUser)
        ->get(route('client.documents.show', $document))
        ->assertOk()
        ->assertSee('Facture à consulter')
        ->assertSee('Télécharger')
        ->assertSee('FAC-2026-001')
        ->assertSee('50 000 FCFA')
        ->assertSee('Retour');
});

test('client can view a compte rendu document with download button', function () {
    Storage::disk('r2')->put('documents/compte_rendu.pdf', 'fake pdf content');

    $document = Document::create([
        'client_id' => $this->client->id,
        'nom' => 'Compte rendu intervention',
        'path' => 'documents/compte_rendu.pdf',
        'type' => 'compte_rendu',
        'mime_type' => 'application/pdf',
        'size' => 1000,
        'status' => Document::STATUS_SENT,
        'metadata' => [
            'titre' => 'Réparation fuite',
            'description' => 'Remplacement du joint défectueux.',
            'date_intervention' => '2026-08-02',
            'duree' => 2.5,
        ],
    ]);

    $this->actingAs($this->clientUser)
        ->get(route('client.documents.show', $document))
        ->assertOk()
        ->assertSee('Compte rendu à consulter')
        ->assertSee('Télécharger')
        ->assertSee('Réparation fuite')
        ->assertSee('Retour');
});

test('client cannot return a non-devis document', function () {
    $document = Document::create([
        'client_id' => $this->client->id,
        'nom' => 'Facture',
        'path' => 'documents/facture.pdf',
        'type' => 'facture',
        'mime_type' => 'application/pdf',
        'size' => 1000,
        'status' => Document::STATUS_SENT,
    ]);

    $this->actingAs($this->clientUser)
        ->post(route('client.documents.return', $document), [
            'attestation' => '1',
        ])
        ->assertForbidden();
});

test('client cannot return an already accepted devis', function () {
    $document = Document::create([
        'client_id' => $this->client->id,
        'nom' => 'Devis rénovation',
        'path' => 'documents/devis.pdf',
        'type' => 'devis',
        'mime_type' => 'application/pdf',
        'size' => 1000,
        'status' => Document::STATUS_SIGNED,
        'signed_at' => now(),
        'signature_data' => ['signature' => null, 'returned_without_signature' => true],
    ]);

    $this->actingAs($this->clientUser)
        ->post(route('client.documents.return', $document), [
            'attestation' => '1',
        ])
        ->assertRedirect()
        ->assertSessionHas('info');
});

test('client can view documents index', function () {
    $document = Document::create([
        'client_id' => $this->client->id,
        'nom' => 'Devis rénovation',
        'path' => 'documents/devis.pdf',
        'type' => 'devis',
        'mime_type' => 'application/pdf',
        'size' => 1000,
        'status' => Document::STATUS_SENT,
    ]);

    $this->actingAs($this->clientUser)
        ->get(route('client.documents.index'))
        ->assertOk()
        ->assertSee('Devis rénovation')
        ->assertSee('Consulter');
});

test('documents index page shows correct create labels', function () {
    $this->actingAs($this->artisanUser)
        ->get(route('artisan.documents.index'))
        ->assertOk()
        ->assertSee('Nouveau devis')
        ->assertSee('Nouvelle facture')
        ->assertSee('Nouveau compte rendu')
        ->assertDontSee('Nouvelle devis')
        ->assertDontSee('Nouvelle compte rendu');
});

test('document model canExport returns false for unsigned devis', function () {
    $document = Document::create([
        'client_id' => $this->client->id,
        'nom' => 'Devis',
        'path' => 'documents/devis.pdf',
        'type' => 'devis',
        'mime_type' => 'application/pdf',
        'size' => 1000,
        'status' => Document::STATUS_SENT,
    ]);

    expect($document->canExport())->toBeFalse();
});

test('document model canExport returns true for returned devis', function () {
    $document = Document::create([
        'client_id' => $this->client->id,
        'nom' => 'Devis',
        'path' => 'documents/devis.pdf',
        'type' => 'devis',
        'mime_type' => 'application/pdf',
        'size' => 1000,
        'status' => Document::STATUS_SIGNED,
    ]);

    expect($document->canExport())->toBeTrue();
});

test('document model canExport returns true for non-devis documents', function () {
    $document = Document::create([
        'client_id' => $this->client->id,
        'nom' => 'Facture',
        'path' => 'documents/facture.pdf',
        'type' => 'facture',
        'mime_type' => 'application/pdf',
        'size' => 1000,
        'status' => Document::STATUS_DRAFT,
    ]);

    expect($document->canExport())->toBeTrue();
});
