<?php

use App\Models\Artisan;
use App\Models\Client;
use App\Models\Document;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(function () {
    Storage::fake('r2');

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

    $this->client = Client::create([
        'artisan_id' => $this->artisan->id,
        'nom' => 'Dupont Jean',
        'telephone' => '+33612345678',
        'email' => 'jean@example.com',
        'type' => 'particulier',
    ]);
});

test('documents index page loads successfully', function () {
    $this->actingAs($this->user)
        ->get(route('artisan.documents.index'))
        ->assertOk();
});

test('documents index page is forbidden when user has no artisan profile', function () {
    $userWithoutArtisan = User::factory()->create();

    $this->actingAs($userWithoutArtisan)
        ->get(route('artisan.documents.index'))
        ->assertForbidden();
});

test('artisan can store a document', function () {
    Pdf::shouldReceive('loadHTML')->once()->andReturnSelf();
    Pdf::shouldReceive('setPaper')->once()->andReturnSelf();
    Pdf::shouldReceive('setOptions')->once()->andReturnSelf();
    Pdf::shouldReceive('output')->once()->andReturn('%PDF-1.4 fake pdf content');

    $this->actingAs($this->user)
        ->post(route('artisan.documents.store'), [
            'client_id' => $this->client->id,
            'nom' => 'Devis rénovation',
            'type' => 'devis',
            'reference_devis' => 'DEV-001',
            'date_emission_devis' => '2026-08-03',
            'lignes' => [
                ['libelle' => 'Pose de carrelage', 'quantite' => 10, 'prix_unitaire' => 50],
            ],
        ])
        ->assertRedirect(route('artisan.documents.index'))
        ->assertSessionHas('success');

    expect(Document::count())->toBe(1);
    $document = Document::first();
    expect($document->client_id)->toBe($this->client->id);
    expect($document->type)->toBe('devis');
    expect($document->nom)->toBe('Devis rénovation');
    expect($document->date_modification)->not->toBeNull();
    expect($document->path)->toStartWith('documents/devis_');
});

test('artisan can store a facture without uploading a file', function () {
    Pdf::shouldReceive('loadHTML')->once()->andReturnSelf();
    Pdf::shouldReceive('setPaper')->once()->andReturnSelf();
    Pdf::shouldReceive('setOptions')->once()->andReturnSelf();
    Pdf::shouldReceive('output')->once()->andReturn('%PDF-1.4 fake pdf content');

    $this->actingAs($this->user)
        ->post(route('artisan.documents.store'), [
            'client_id' => $this->client->id,
            'nom' => 'Facture travaux',
            'type' => 'facture',
            'numero_facture' => 'FAC-2026-001',
            'date_emission_facture' => '2026-08-03',
            'montant_ht' => 50000,
            'tva' => 18,
            'montant_ttc' => 59000,
        ])
        ->assertRedirect(route('artisan.documents.index'))
        ->assertSessionHas('success');

    expect(Document::count())->toBe(1);
    $document = Document::first();
    expect($document->type)->toBe('facture');
    expect($document->metadata['numero'])->toBe('FAC-2026-001');
    expect($document->path)->toStartWith('documents/facture_');
});

test('artisan can store a compte rendu without uploading a file', function () {
    Pdf::shouldReceive('loadHTML')->once()->andReturnSelf();
    Pdf::shouldReceive('setPaper')->once()->andReturnSelf();
    Pdf::shouldReceive('setOptions')->once()->andReturnSelf();
    Pdf::shouldReceive('output')->once()->andReturn('%PDF-1.4 fake pdf content');

    $this->actingAs($this->user)
        ->post(route('artisan.documents.store'), [
            'client_id' => $this->client->id,
            'nom' => 'Compte rendu intervention',
            'type' => 'compte_rendu',
            'titre_compte_rendu' => 'Réparation fuite',
            'description_compte_rendu' => 'Remplacement du joint défectueux.',
            'date_intervention' => '2026-08-02',
            'duree' => 2.5,
        ])
        ->assertRedirect(route('artisan.documents.index'))
        ->assertSessionHas('success');

    expect(Document::count())->toBe(1);
    $document = Document::first();
    expect($document->type)->toBe('compte_rendu');
    expect($document->metadata['titre'])->toBe('Réparation fuite');
    expect($document->path)->toStartWith('documents/compte_rendu_');
});

test('artisan cannot store a document for another artisans client', function () {
    $otherArtisan = Artisan::create([
        'user_id' => User::factory()->create()->id,
        'business_name' => 'Other Artisan',
        'slug' => 'other-artisan',
        'profession' => 'Electricien',
        'phone' => '+33987654321',
        'city' => 'Lyon',
        'verified' => true,
        'is_active' => true,
    ]);

    $otherClient = Client::create([
        'artisan_id' => $otherArtisan->id,
        'nom' => 'Other Client',
        'telephone' => '+33699999999',
    ]);

    $file = UploadedFile::fake()->create('facture.pdf', 100, 'application/pdf');

    $this->actingAs($this->user)
        ->post(route('artisan.documents.store'), [
            'client_id' => $otherClient->id,
            'nom' => 'Facture',
            'type' => 'facture',
            'fichier' => $file,
        ])
        ->assertNotFound();
});

test('artisan can view the edit page for a document', function () {
    $document = Document::create([
        'client_id' => $this->client->id,
        'nom' => 'Devis à modifier',
        'path' => 'documents/test.pdf',
        'type' => 'devis',
        'mime_type' => 'application/pdf',
        'size' => 1000,
    ]);

    $this->actingAs($this->user)
        ->get(route('artisan.documents.edit', $document))
        ->assertOk()
        ->assertSee('Modifier document')
        ->assertSee('Devis à modifier');
});

test('artisan can update a document', function () {
    $document = Document::create([
        'client_id' => $this->client->id,
        'nom' => 'Ancien nom',
        'path' => 'documents/test.pdf',
        'type' => 'devis',
        'mime_type' => 'application/pdf',
        'size' => 1000,
    ]);

    $this->actingAs($this->user)
        ->put(route('artisan.documents.update', $document), [
            'nom' => 'Nouveau nom',
            'client_id' => $this->client->id,
            'type' => 'facture',
        ])
        ->assertRedirect(route('artisan.documents.index'))
        ->assertSessionHas('success');

    $document->refresh();
    expect($document->nom)->toBe('Nouveau nom');
    expect($document->type)->toBe('facture');
    expect($document->date_modification)->not->toBeNull();
});

test('artisan can delete a document', function () {
    Storage::fake('r2');
    Storage::disk('r2')->put('documents/test.pdf', 'content');

    $document = Document::create([
        'client_id' => $this->client->id,
        'nom' => 'Document à supprimer',
        'path' => 'documents/test.pdf',
        'type' => 'devis',
        'mime_type' => 'application/pdf',
        'size' => 1000,
    ]);

    $this->actingAs($this->user)
        ->delete(route('artisan.documents.destroy', $document))
        ->assertRedirect()
        ->assertSessionHas('success');

    expect(Document::count())->toBe(0);
    Storage::disk('r2')->assertMissing('documents/test.pdf');
});

test('documents can be filtered by type', function () {
    Document::create([
        'client_id' => $this->client->id,
        'nom' => 'Devis 1',
        'path' => 'documents/devis1.pdf',
        'type' => 'devis',
        'mime_type' => 'application/pdf',
        'size' => 1000,
    ]);

    Document::create([
        'client_id' => $this->client->id,
        'nom' => 'Facture 1',
        'path' => 'documents/facture1.pdf',
        'type' => 'facture',
        'mime_type' => 'application/pdf',
        'size' => 1000,
    ]);

    $this->actingAs($this->user)
        ->get(route('artisan.documents.index', ['type' => 'devis']))
        ->assertOk()
        ->assertSee('Devis 1')
        ->assertDontSee('Facture 1');
});

test('documents can be filtered by client', function () {
    $client2 = Client::create([
        'artisan_id' => $this->artisan->id,
        'nom' => 'Martin Sophie',
        'telephone' => '+33611111111',
        'email' => 'sophie@example.com',
    ]);

    Document::create([
        'client_id' => $this->client->id,
        'nom' => 'Doc client 1',
        'path' => 'documents/doc1.pdf',
        'type' => 'devis',
        'mime_type' => 'application/pdf',
        'size' => 1000,
    ]);

    Document::create([
        'client_id' => $client2->id,
        'nom' => 'Doc client 2',
        'path' => 'documents/doc2.pdf',
        'type' => 'facture',
        'mime_type' => 'application/pdf',
        'size' => 1000,
    ]);

    $this->actingAs($this->user)
        ->get(route('artisan.documents.index', ['client_id' => $this->client->id]))
        ->assertOk()
        ->assertSee('Doc client 1')
        ->assertDontSee('Doc client 2');
});

test('document update tracks modification date', function () {
    $document = Document::create([
        'client_id' => $this->client->id,
        'nom' => 'Original',
        'path' => 'documents/test.pdf',
        'type' => 'devis',
        'mime_type' => 'application/pdf',
        'size' => 1000,
    ]);

    expect($document->date_modification)->toBeNull();

    $this->actingAs($this->user)
        ->put(route('artisan.documents.update', $document), [
            'nom' => 'Modifié',
            'client_id' => $this->client->id,
            'type' => 'attestation',
        ]);

    $document->refresh();
    expect($document->date_modification)->not->toBeNull();
});

test('artisan can send a document to another client', function () {
    $client2 = Client::create([
        'artisan_id' => $this->artisan->id,
        'nom' => 'Martin Sophie',
        'telephone' => '+33611111111',
        'email' => 'sophie@example.com',
    ]);

    $document = Document::create([
        'client_id' => $this->client->id,
        'nom' => 'Doc à envoyer',
        'path' => 'documents/test.pdf',
        'type' => 'devis',
        'mime_type' => 'application/pdf',
        'size' => 1000,
    ]);

    $this->actingAs($this->user)
        ->post(route('artisan.documents.send-to-client', $document), [
            'client_id' => $client2->id,
        ])
        ->assertRedirect()
        ->assertSessionHas('success');

    $document->refresh();
    expect($document->client_id)->toBe($client2->id);
});
