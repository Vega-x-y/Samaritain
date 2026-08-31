<?php

use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use App\Models\Artisan;
use App\Models\ArtisanRequest;
use App\Models\ArtisanWallet;
use App\Models\Client;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\OwnerWallet;
use App\Models\Setting;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

function makeChatScenario(): array
{
    $artisanUser = User::factory()->create();
    $clientUser = User::factory()->create();

    $artisan = Artisan::create([
        'user_id' => $artisanUser->id,
        'business_name' => 'Test Artisan',
        'slug' => 'test-artisan-'.Str::random(4),
        'profession' => 'Plombier',
        'phone' => '+33123456789',
        'city' => 'Paris',
        'verified' => true,
        'is_active' => true,
    ]);

    $client = Client::create([
        'artisan_id' => $artisan->id,
        'user_id' => $clientUser->id,
        'nom' => 'Client Test',
        'telephone' => '0612345678',
    ]);

    $conversation = Conversation::create([
        'artisan_id' => $artisan->id,
        'client_id' => $client->id,
        'sujet' => 'Test',
    ]);

    $request = ArtisanRequest::create([
        'artisan_id' => $artisan->id,
        'user_id' => $clientUser->id,
        'type' => 'paiement',
        'statut' => 'acceptee',
        'payment_status' => 'UNPAID',
        'total_amount' => 50000,
        'down_payment_amount' => 10000,
        'message' => 'Lien de paiement envoyé via la messagerie.',
    ]);

    $artisanRequest = $request;

    Message::create([
        'conversation_id' => $conversation->id,
        'expediteur_type' => 'artisan',
        'expediteur_id' => $artisan->id,
        'expediteur_nom' => $artisanUser->name,
        'contenu' => null,
        'type' => 'payment_link',
        'metadata' => [
            'artisan_request_id' => $artisanRequest->id,
            'total_amount' => 50000,
            'down_payment_amount' => 10000,
            'deposit_url' => route('transactions.deposit', ['artisan_request' => $artisanRequest->id]),
        ],
        'lu' => false,
    ]);

    return compact('artisanUser', 'clientUser', 'artisan', 'client', 'conversation', 'artisanRequest');
}

beforeEach(function () {
    config([
        'services.pawapay.api_url' => 'https://api.sandbox.pawapay.io/v2',
        'services.pawapay.country' => 'COG',
        'services.pawapay.currency' => 'XAF',
        'services.pawapay.dial_code' => '242',
        'services.pawapay.api_key' => 'test-token',
    ]);

    Http::fake([
        '*/active-conf*' => Http::response([
            'countries' => [[
                'country' => 'COG',
                'prefix' => '242',
                'providers' => [
                    ['provider' => 'MTN_MOMO_COG', 'displayName' => 'MTN Mobile Money'],
                ],
            ]],
        ], 200),
    ]);
});

test('the client sees the artisan payment link card in the conversation', function () {
    $s = makeChatScenario();
    $this->actingAs($s['clientUser']);

    $this->get(route('client.messagerie.show', $s['conversation']))
        ->assertOk()
        ->assertSee('Demande de paiement')
        ->assertSee('Payer')
        ->assertSee('acompte')
        ->assertSee('50 000')
        ->assertSee('10 000');
});

test('the deposit form locks the amount to the down payment and carries the artisan request', function () {
    $s = makeChatScenario();
    $this->actingAs($s['clientUser']);

    $this->get(route('transactions.deposit', ['artisan_request' => $s['artisanRequest']->id]))
        ->assertOk()
        ->assertViewIs('transactions.deposit-form')
        ->assertViewHas('artisanRequest', fn ($artisanRequest) => $artisanRequest->is($s['artisanRequest']))
        ->assertSee('Payer l\'acompte');
});

test('an artisan down payment deposit is attached to the artisan request and credited to the artisan wallet', function () {
    $s = makeChatScenario();
    $this->actingAs($s['clientUser']);

    Setting::setValue('artisan_commission_percent', 5);

    $ownerWallet = OwnerWallet::create([
        'owner_id' => $s['clientUser']->id,
        'available_balance' => 0,
        'reserved_balance' => 0,
    ]);

    Http::fake(['*/v2/deposits' => Http::response([
        'depositId' => Str::uuid()->toString(),
        'status' => 'ACCEPTED',
    ], 200)]);

    $response = $this->post(route('transactions.deposit'), [
        'artisan_request' => $s['artisanRequest']->id,
        'amount' => 10000,
        'phone' => '064567890',
        'provider' => 'MTN_MOMO_COG',
    ]);

    $response->assertSessionHasNoErrors();

    $transaction = Transaction::first();

    expect($transaction)->not->toBeNull()
        ->and($transaction->artisan_request_id)->toBe($s['artisanRequest']->id)
        ->and($transaction->amount)->toBe(10000)
        ->and($transaction->type)->toBe(TransactionType::DEPOSIT);

    $depositId = $transaction->deposit_id;

    Http::fake(["*/v2/deposits/{$depositId}" => Http::response([
        'status' => 'FOUND',
        'data' => [
            'depositId' => $depositId,
            'status' => 'COMPLETED',
            'amount' => '10000',
            'currency' => 'XAF',
        ],
    ], 200)]);

    $this->get(route('transactions.deposit.status', $transaction));

    $wallet = ArtisanWallet::where('artisan_id', $s['artisan']->id)->first();

    expect($wallet)->not->toBeNull()
        ->and($wallet->available_balance)->toBe(9500)
        ->and($s['artisanRequest']->refresh()->payment_status)->toBe('DOWN_PAYMENT_PAID')
        ->and($ownerWallet->refresh()->available_balance)->toBe(0)
        ->and($transaction->refresh()->status)->toBe(TransactionStatus::COMPLETED);
});
