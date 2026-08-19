<?php

use App\Models\OwnerDocument;
use App\Models\Property;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(function () {
    Storage::fake('r2');

    $this->owner = User::factory()->create();
});

test('owner can view documents index', function () {
    OwnerDocument::create([
        'name' => 'Facture électricité',
        'category' => 'invoice',
        'file_path' => 'documents/owner/facture.pdf',
        'file_size' => 256000,
        'created_by' => $this->owner->id,
    ]);

    Storage::disk('r2')->put('documents/owner/facture.pdf', 'fake pdf content');

    $this->actingAs($this->owner)
        ->get(route('owner.documents.index'))
        ->assertOk()
        ->assertSee('Facture électricité')
        ->assertSee('Consulter')
        ->assertSee('Télécharger');
});

test('owner can view a single document', function () {
    Storage::disk('r2')->put('documents/owner/facture.pdf', 'fake pdf content');

    $document = OwnerDocument::create([
        'name' => 'Facture électricité',
        'category' => 'invoice',
        'file_path' => 'documents/owner/facture.pdf',
        'file_size' => 256000,
        'created_by' => $this->owner->id,
    ]);

    $this->actingAs($this->owner)
        ->get(route('owner.documents.show', $document))
        ->assertOk()
        ->assertSee('Facture électricité')
        ->assertSee('Aperçu du document')
        ->assertSee('Télécharger')
        ->assertSee('Supprimer');
});

test('owner can view image documents with image preview', function () {
    Storage::disk('r2')->put('documents/owner/visite.jpg', 'fake image content');

    $document = OwnerDocument::create([
        'name' => 'Photo visite',
        'category' => 'inspection',
        'file_path' => 'documents/owner/visite.jpg',
        'file_size' => 512000,
        'created_by' => $this->owner->id,
    ]);

    $this->actingAs($this->owner)
        ->get(route('owner.documents.show', $document))
        ->assertOk()
        ->assertSee('src="', false) // image tag should render
        ->assertSee('Facture électricité', false);
});

test('another owner cannot view a document they do not own', function () {
    Storage::disk('r2')->put('documents/owner/facture.pdf', 'fake pdf content');

    $document = OwnerDocument::create([
        'name' => 'Facture électricité',
        'category' => 'invoice',
        'file_path' => 'documents/owner/facture.pdf',
        'file_size' => 256000,
        'created_by' => $this->owner->id,
    ]);

    $otherUser = User::factory()->create();

    $this->actingAs($otherUser)
        ->get(route('owner.documents.show', $document))
        ->assertForbidden();
});

test('owner can view document with property', function () {
    $property = Property::factory()->create(['created_by' => $this->owner->id]);

    Storage::disk('r2')->put('documents/owner/bail.pdf', 'fake pdf content');

    $document = OwnerDocument::create([
        'name' => 'Bail signé',
        'category' => 'other',
        'file_path' => 'documents/owner/bail.pdf',
        'file_size' => 1024000,
        'property_id' => $property->id,
        'created_by' => $this->owner->id,
    ]);

    $this->actingAs($this->owner)
        ->get(route('owner.documents.show', $document))
        ->assertOk()
        ->assertSee($property->title);
});

test('owner document show route is accessible', function () {
    Storage::disk('r2')->put('documents/owner/test.pdf', 'fake pdf content');

    $document = OwnerDocument::create([
        'name' => 'Test document',
        'category' => 'receipt',
        'file_path' => 'documents/owner/test.pdf',
        'file_size' => 100000,
        'created_by' => $this->owner->id,
    ]);

    $response = $this->actingAs($this->owner)
        ->get(route('owner.documents.show', $document));

    $response->assertOk();
});
