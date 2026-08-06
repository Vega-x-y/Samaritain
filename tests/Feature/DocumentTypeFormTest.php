<?php

use App\Models\Artisan;
use App\Models\Client;
use App\Models\Document;
use App\Models\User;
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

test('devis create page loads successfully', function () {
    $this->actingAs($this->user)
        ->get(route('artisan.documents.create', ['type' => 'devis']))
        ->assertOk()
        ->assertSee('Nouveau devis')
        ->assertSee('Référence devis')
        ->assertSee('Lignes du devis');
});

test('facture create page loads successfully', function () {
    $this->actingAs($this->user)
        ->get(route('artisan.documents.create', ['type' => 'facture']))
        ->assertOk()
        ->assertSee('Nouvelle facture')
        ->assertSee('Numéro de facture')
        ->assertSee('Montant HT');
});

test('compte rendu create page loads successfully', function () {
    $this->actingAs($this->user)
        ->get(route('artisan.documents.create', ['type' => 'compte_rendu']))
        ->assertOk()
        ->assertSee('Nouveau compte rendu')
        ->assertSee('Date d\'intervention', false)
        ->assertSee('Durée');
});

test('attestation create page loads successfully', function () {
    $this->actingAs($this->user)
        ->get(route('artisan.documents.create', ['type' => 'attestation']))
        ->assertOk()
        ->assertSee('Nouvelle attestation')
        ->assertSee('Titre de l\'attestation', false)
        ->assertSee('Date d\'émission', false);
});

test('artisan can create a devis document via blade form', function () {
    $file = UploadedFile::fake()->create('devis.pdf', 100, 'application/pdf');

    $this->actingAs($this->user)
        ->post(route('artisan.documents.store'), [
            'client_id' => $this->client->id,
            'nom' => 'Devis rénovation',
            'type' => 'devis',
            'fichier' => $file,
            'reference_devis' => 'DEV-001',
            'date_emission_devis' => '2025-01-15',
            'lignes' => [
                [
                    'libelle' => 'Réparation canalisation',
                    'quantite' => 2,
                    'prix_unitaire' => 15000,
                ],
            ],
        ])
        ->assertRedirect(route('artisan.documents.index'))
        ->assertSessionHas('success');

    expect(Document::count())->toBe(1);
    $document = Document::first();
    expect($document->type)->toBe('devis');
    expect($document->nom)->toBe('Devis rénovation');
    expect($document->client_id)->toBe($this->client->id);
    expect($document->metadata['reference'])->toBe('DEV-001');
    expect($document->metadata['lignes'])->toHaveCount(1);
    expect($document->metadata['lignes'][0]['libelle'])->toBe('Réparation canalisation');
});

test('artisan can create a facture document via blade form', function () {
    $file = UploadedFile::fake()->create('facture.pdf', 100, 'application/pdf');

    $this->actingAs($this->user)
        ->post(route('artisan.documents.store'), [
            'client_id' => $this->client->id,
            'nom' => 'Facture électricité',
            'type' => 'facture',
            'fichier' => $file,
            'numero_facture' => 'FAC-001',
            'date_emission_facture' => '2025-01-15',
            'montant_ht' => 100000,
            'tva' => 20,
            'montant_ttc' => 120000,
        ])
        ->assertRedirect(route('artisan.documents.index'))
        ->assertSessionHas('success');

    expect(Document::count())->toBe(1);
    $document = Document::first();
    expect($document->type)->toBe('facture');
    expect($document->metadata['numero'])->toBe('FAC-001');
    expect($document->metadata['montant_ht'])->toEqual(100000.0);
    expect($document->metadata['tva'])->toEqual(20.0);
    expect($document->metadata['montant_ttc'])->toEqual(120000.0);
});

test('artisan can create a compte rendu document via blade form', function () {
    $file = UploadedFile::fake()->create('rapport.pdf', 100, 'application/pdf');

    $this->actingAs($this->user)
        ->post(route('artisan.documents.store'), [
            'client_id' => $this->client->id,
            'nom' => 'Rapport d\'intervention',
            'type' => 'compte_rendu',
            'fichier' => $file,
            'titre_compte_rendu' => 'Réparation canalisation salle de bain',
            'description_compte_rendu' => 'Remplacement de la canalisation défectueuse',
            'date_intervention' => '2025-01-15',
            'duree' => 3.5,
        ])
        ->assertRedirect(route('artisan.documents.index'))
        ->assertSessionHas('success');

    expect(Document::count())->toBe(1);
    $document = Document::first();
    expect($document->type)->toBe('compte_rendu');
    expect($document->metadata['titre'])->toBe('Réparation canalisation salle de bain');
    expect($document->metadata['duree'])->toBe(3.5);
});

test('artisan can create an attestation document via blade form', function () {
    $file = UploadedFile::fake()->create('attestation.pdf', 100, 'application/pdf');

    $this->actingAs($this->user)
        ->post(route('artisan.documents.store'), [
            'client_id' => $this->client->id,
            'nom' => 'Attestation de travaux',
            'type' => 'attestation',
            'fichier' => $file,
            'reference_attestation' => 'ATT-001',
            'titre_attestation' => 'Attestation de travaux réalisés',
            'description_attestation' => 'Les travaux de rénovation ont été réalisés conformément aux normes.',
            'date_emission_attestation' => '2025-01-15',
        ])
        ->assertRedirect(route('artisan.documents.index'))
        ->assertSessionHas('success');

    expect(Document::count())->toBe(1);
    $document = Document::first();
    expect($document->type)->toBe('attestation');
    expect($document->metadata['reference'])->toBe('ATT-001');
    expect($document->metadata['titre'])->toBe('Attestation de travaux réalisés');
    expect($document->metadata['description'])->toBe('Les travaux de rénovation ont été réalisés conformément aux normes.');
    expect($document->metadata['date_emission'])->toBe('2025-01-15');
});

test('devis form accepts empty reference and lignes', function () {
    $file = UploadedFile::fake()->create('devis.pdf', 100, 'application/pdf');

    $this->actingAs($this->user)
        ->post(route('artisan.documents.store'), [
            'client_id' => $this->client->id,
            'nom' => 'Devis',
            'type' => 'devis',
            'fichier' => $file,
            'reference_devis' => '',
            'date_emission_devis' => '2025-01-15',
        ])
        ->assertRedirect(route('artisan.documents.index'))
        ->assertSessionHas('success');
});

test('facture form accepts empty numero and montant', function () {
    $file = UploadedFile::fake()->create('facture.pdf', 100, 'application/pdf');

    $this->actingAs($this->user)
        ->post(route('artisan.documents.store'), [
            'client_id' => $this->client->id,
            'nom' => 'Facture',
            'type' => 'facture',
            'fichier' => $file,
            'numero_facture' => '',
            'date_emission_facture' => '2025-01-15',
            'montant_ht' => null,
        ])
        ->assertRedirect(route('artisan.documents.index'))
        ->assertSessionHas('success');
});

test('compte rendu form accepts empty titre and description', function () {
    $file = UploadedFile::fake()->create('rapport.pdf', 100, 'application/pdf');

    $this->actingAs($this->user)
        ->post(route('artisan.documents.store'), [
            'client_id' => $this->client->id,
            'nom' => 'Rapport',
            'type' => 'compte_rendu',
            'fichier' => $file,
            'titre_compte_rendu' => '',
            'description_compte_rendu' => '',
            'date_intervention' => '2025-01-15',
            'duree' => 2,
        ])
        ->assertRedirect(route('artisan.documents.index'))
        ->assertSessionHas('success');
});

test('attestation form accepts empty reference and optional fields', function () {
    $file = UploadedFile::fake()->create('attestation.pdf', 100, 'application/pdf');

    $this->actingAs($this->user)
        ->post(route('artisan.documents.store'), [
            'client_id' => $this->client->id,
            'nom' => 'Attestation',
            'type' => 'attestation',
            'fichier' => $file,
            'reference_attestation' => '',
            'titre_attestation' => '',
            'description_attestation' => '',
            'date_emission_attestation' => '2025-01-15',
        ])
        ->assertRedirect(route('artisan.documents.index'))
        ->assertSessionHas('success');
});

test('invalid type returns 404', function () {
    $this->actingAs($this->user)
        ->get(route('artisan.documents.create', ['type' => 'invalid']))
        ->assertNotFound();
});

test('document counts are passed to the view', function () {
    Document::create([
        'client_id' => $this->client->id,
        'nom' => 'Devis 1',
        'path' => 'documents/test.pdf',
        'type' => 'devis',
        'mime_type' => 'application/pdf',
        'size' => 1000,
    ]);

    Document::create([
        'client_id' => $this->client->id,
        'nom' => 'Facture 1',
        'path' => 'documents/test2.pdf',
        'type' => 'facture',
        'mime_type' => 'application/pdf',
        'size' => 1000,
    ]);

    $this->actingAs($this->user)
        ->get(route('artisan.documents.index'))
        ->assertOk()
        ->assertSee('1')
        ->assertSee('0');
});
