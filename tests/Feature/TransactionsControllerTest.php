<?php

use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use App\Models\OwnerWallet;
use App\Models\Transaction;
use App\Models\User;
use App\Services\OwnerWalletService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

function makeWallet(User $user): OwnerWallet
{
    return OwnerWallet::create([
        'owner_id' => $user->id,
        'available_balance' => 0,
        'reserved_balance' => 0,
    ]);
}

beforeEach(function () {
    config([
        'services.pawapay.api_url' => 'https://api.sandbox.pawapay.io/v2',
        'services.pawapay.country' => 'COG',
        'services.pawapay.currency' => 'XAF',
        'services.pawapay.dial_code' => '242',
        'services.pawapay.api_key' => 'test-token',
    ]);
});

test('deposit form is reachable and lists available providers', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    Http::fake([
        '*/v2/active-conf' => Http::response([
            'countries' => [[
                'country' => 'COG',
                'prefix' => '242',
                'providers' => [
                    ['provider' => 'MTN_MOMO_COG', 'displayName' => 'MTN Mobile Money'],
                ],
            ]],
        ], 200),
    ]);

    $response = $this->get(route('transactions.deposit'));

    $response->assertOk()
        ->assertViewHas('payment_config')
        ->assertSee('MTN Mobile Money');
});

test('init deposit accepts a valid deposit and redirects to status', function () {
    $user = User::factory()->create();
    makeWallet($user);
    $this->actingAs($user);

    Http::fake([
        '*/v2/deposits' => Http::response([
            'depositId' => Str::uuid()->toString(),
            'status' => 'ACCEPTED',
            'created' => now()->toIso8601String(),
        ], 200),
    ]);

    $response = $this->post(route('transactions.deposit'), [
        'amount' => 100,
        'phone' => '064567890',
        'provider' => 'MTN_MOMO_COG',
    ]);

    $response->assertRedirect(route('transactions.deposit.status', Transaction::first()));

    $transaction = Transaction::first();
    expect($transaction)->not->toBeNull()
        ->and($transaction->type)->toBe(TransactionType::DEPOSIT)
        ->and($transaction->amount)->toBe(10000)
        ->and($transaction->deposit_id)->not->toBeNull();
});

test('init deposit marks the transaction as rejected when API rejects', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    Http::fake([
        '*/v2/deposits' => Http::response([
            'depositId' => Str::uuid()->toString(),
            'status' => 'REJECTED',
            'failureReason' => [
                'failureCode' => 'INVALID_PHONE_NUMBER',
                'failureMessage' => "The phone number is invalid for the provider 'MTN_MOMO_COG'.",
            ],
        ], 200),
    ]);

    $this->post(route('transactions.deposit'), [
        'amount' => 100,
        'phone' => '064567890',
        'provider' => 'MTN_MOMO_COG',
    ]);

    $transaction = Transaction::first();
    expect($transaction)->not->toBeNull()
        ->and($transaction->status)->toBe(TransactionStatus::REJECTED)
        ->and($transaction->failure_reason)->toContain('MTN_MOMO_COG');
});

test('failed deposit status renders a link to the deposit form', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $transaction = Transaction::create([
        'transaction_id' => Str::uuid()->toString(),
        'user_id' => $user->id,
        'type' => TransactionType::DEPOSIT,
        'status' => TransactionStatus::REJECTED,
        'amount' => 10000,
        'currency' => 'XAF',
        'deposit_id' => Str::uuid()->toString(),
        'provider' => 'MTN_MOMO_COG',
    ]);

    Http::fake([
        "*/v2/deposits/{$transaction->deposit_id}" => Http::response([
            'status' => 'REJECTED',
        ], 200),
    ]);

    $this->get(route('transactions.deposit.status', $transaction))
        ->assertOk()
        ->assertSee(route('transactions.deposit'), false);
});

test('checking a completed deposit status credits the wallet once', function () {
    $user = User::factory()->create();
    $wallet = makeWallet($user);
    $this->actingAs($user);

    $depositId = Str::uuid()->toString();

    $transaction = Transaction::create([
        'transaction_id' => Str::uuid()->toString(),
        'user_id' => $user->id,
        'type' => TransactionType::DEPOSIT,
        'status' => TransactionStatus::PENDING,
        'amount' => 5000,
        'currency' => 'XAF',
        'deposit_id' => $depositId,
        'provider' => 'MTN_MOMO_COG',
    ]);

    Http::fake([
        "*/v2/deposits/{$depositId}" => Http::response([
            'status' => 'FOUND',
            'data' => [
                'depositId' => $depositId,
                'status' => 'COMPLETED',
                'amount' => '50.00',
                'currency' => 'XAF',
            ],
        ], 200),
    ]);

    $this->get(route('transactions.deposit.status', $transaction));
    $this->get(route('transactions.deposit.status', $transaction));

    $wallet->refresh();

    expect($transaction->refresh()->status)->toBe(TransactionStatus::COMPLETED)
        ->and($wallet->available_balance)->toBe(5000);
});

test('a user cannot view another users transaction status', function () {
    $owner = User::factory()->create();
    $other = User::factory()->create();

    $transaction = Transaction::create([
        'transaction_id' => Str::uuid()->toString(),
        'user_id' => $owner->id,
        'type' => TransactionType::DEPOSIT,
        'status' => TransactionStatus::PENDING,
        'amount' => 5000,
        'currency' => 'XAF',
        'deposit_id' => Str::uuid()->toString(),
    ]);

    $this->actingAs($other);

    $this->get(route('transactions.deposit.status', $transaction))->assertForbidden();
});

test('creditDeposit is idempotent and does not double credit', function () {
    $user = User::factory()->create();
    $wallet = makeWallet($user);

    $transaction = Transaction::create([
        'transaction_id' => Str::uuid()->toString(),
        'user_id' => $user->id,
        'type' => TransactionType::DEPOSIT,
        'status' => TransactionStatus::COMPLETED,
        'amount' => 2500,
        'currency' => 'XAF',
    ]);

    $service = app(OwnerWalletService::class);
    $service->creditDeposit($transaction);
    $service->creditDeposit($transaction);

    $wallet->refresh();

    expect($wallet->available_balance)->toBe(2500);
});
