<?php

use App\Livewire\Messenger\ChatWindow;
use App\Livewire\Messenger\ConversationList;
use App\Models\Contract;
use App\Models\OwnerConversation;
use App\Models\OwnerMessage;
use App\Models\Property;
use App\Models\User;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;

test('creates an owner tenant conversation from an active contract', function () {
    $owner = User::factory()->create();
    $tenant = User::factory()->create();
    $property = Property::factory()->create(['created_by' => $owner->id]);

    $contract = Contract::factory()->create([
        'property_id' => $property->id,
        'created_by' => $owner->id,
        'tenant_email' => $tenant->email,
        'status' => 'active',
    ]);

    $this->actingAs($owner);

    Livewire::test(ConversationList::class)
        ->assertSee($tenant->name)
        ->assertSee($property->title);

    $conversation = OwnerConversation::where('contract_id', $contract->id)->firstOrFail();

    expect($conversation->owner_id)->toBe($owner->id)
        ->and($conversation->tenant_id)->toBe($tenant->id);
});

test('owner and tenant can exchange messages in the contract conversation', function () {
    Notification::fake();

    $owner = User::factory()->create();
    $tenant = User::factory()->create();
    $property = Property::factory()->create(['created_by' => $owner->id]);
    $contract = Contract::factory()->create([
        'property_id' => $property->id,
        'created_by' => $owner->id,
        'tenant_email' => $tenant->email,
        'status' => 'active',
    ]);
    $conversation = OwnerConversation::create([
        'contract_id' => $contract->id,
        'owner_id' => $owner->id,
        'tenant_id' => $tenant->id,
    ]);

    $this->actingAs($owner);

    Livewire::test(ChatWindow::class)
        ->call('openConversation', $conversation->id)
        ->set('body', 'Bonjour, votre contrat est prêt.')
        ->call('sendMessage');

    $message = OwnerMessage::firstOrFail();

    expect($message->sender_id)->toBe($owner->id)
        ->and($message->body)->toBe('Bonjour, votre contrat est prêt.');

    $this->actingAs($tenant);

    Livewire::test(ChatWindow::class)
        ->call('openConversation', $conversation->id)
        ->set('body', 'Merci pour votre retour.')
        ->call('sendMessage');

    expect(OwnerMessage::count())->toBe(2);
});
