<?php

use App\Models\Artisan;
use App\Models\Client;
use App\Models\Document;
use App\Models\User;
use App\Services\DocumentPdfGenerator;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Foundation\Testing\RefreshDatabase;
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

function mockPdf(): void
{
    Pdf::shouldReceive('loadHTML')
        ->once()
        ->andReturnSelf();

    Pdf::shouldReceive('setPaper')
        ->once()
        ->andReturnSelf();

    Pdf::shouldReceive('setOptions')
        ->once()
        ->andReturnSelf();

    Pdf::shouldReceive('output')
        ->once()
        ->andReturn('%PDF-1.4 fake pdf content');
}

test('it uploads the devis PDF to R2 and updates the document path', function () {
    $document = Document::create([
        'client_id' => $this->client->id,
        'nom' => 'Devis rénovation',
        'path' => 'temp',
        'type' => Document::TYPE_DEVIS,
        'mime_type' => 'application/pdf',
        'size' => 0,
        'metadata' => [
            'reference' => 'DEV-001',
            'date_emission' => '2026-08-03',
            'lignes' => [
                [
                    'libelle' => 'Pose de carrelage',
                    'quantite' => 10,
                    'prix_unitaire' => 50,
                ],
            ],
            'conditions_generales' => null,
        ],
        'status' => Document::STATUS_DRAFT,
    ]);

    mockPdf();

    $generator = new DocumentPdfGenerator;
    $path = $generator->generate($document);

    expect($path)->toStartWith('documents/devis_'.$document->id.'_');

    Storage::disk('r2')->assertExists($path);

    $document->refresh();
    expect($document->path)->toBe($path);
    expect($document->mime_type)->toBe('application/pdf');
    expect($document->size)->toBe(strlen('%PDF-1.4 fake pdf content'));
});

test('it uploads the facture PDF to R2 and updates the document path', function () {
    $document = Document::create([
        'client_id' => $this->client->id,
        'nom' => 'Facture travaux',
        'path' => 'temp',
        'type' => Document::TYPE_FACTURE,
        'mime_type' => 'application/pdf',
        'size' => 0,
        'metadata' => [
            'numero' => 'FAC-2026-001',
            'date_emission' => '2026-08-03',
            'montant_ht' => 50000,
            'tva' => 18,
            'montant_ttc' => 59000,
        ],
        'status' => Document::STATUS_DRAFT,
    ]);

    mockPdf();

    $generator = new DocumentPdfGenerator;
    $path = $generator->generate($document);

    expect($path)->toStartWith('documents/facture_'.$document->id.'_');

    Storage::disk('r2')->assertExists($path);

    $document->refresh();
    expect($document->path)->toBe($path);
    expect($document->mime_type)->toBe('application/pdf');
    expect($document->size)->toBe(strlen('%PDF-1.4 fake pdf content'));
});

test('it uploads the compte rendu PDF to R2 and updates the document path', function () {
    $document = Document::create([
        'client_id' => $this->client->id,
        'nom' => 'Compte rendu intervention',
        'path' => 'temp',
        'type' => Document::TYPE_COMPTE_RENDU,
        'mime_type' => 'application/pdf',
        'size' => 0,
        'metadata' => [
            'titre' => 'Réparation fuite',
            'description' => 'Remplacement du joint défectueux.',
            'date_intervention' => '2026-08-02',
            'duree' => 2.5,
        ],
        'status' => Document::STATUS_DRAFT,
    ]);

    mockPdf();

    $generator = new DocumentPdfGenerator;
    $path = $generator->generate($document);

    expect($path)->toStartWith('documents/compte_rendu_'.$document->id.'_');

    Storage::disk('r2')->assertExists($path);

    $document->refresh();
    expect($document->path)->toBe($path);
    expect($document->mime_type)->toBe('application/pdf');
    expect($document->size)->toBe(strlen('%PDF-1.4 fake pdf content'));
});

test('it throws an exception when the upload fails', function () {
    $document = Document::create([
        'client_id' => $this->client->id,
        'nom' => 'Devis rénovation',
        'path' => 'temp',
        'type' => Document::TYPE_DEVIS,
        'mime_type' => 'application/pdf',
        'size' => 0,
        'metadata' => [
            'reference' => 'DEV-001',
            'date_emission' => '2026-08-03',
            'lignes' => [],
            'conditions_generales' => null,
        ],
        'status' => Document::STATUS_DRAFT,
    ]);

    mockPdf();

    // Simuler un échec d'upload : le disque R2 renvoie false
    Storage::shouldReceive('disk')
        ->with('r2')
        ->andReturn($disk = Mockery::mock(Filesystem::class));
    $disk->shouldReceive('put')->once()->andReturn(false);

    $generator = new DocumentPdfGenerator;

    expect(fn () => $generator->generate($document))
        ->toThrow(Exception::class, 'Erreur lors de l\'upload du PDF vers R2');
});
