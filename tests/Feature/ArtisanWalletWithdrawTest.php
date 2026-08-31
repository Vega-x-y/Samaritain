<?php

use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use App\Models\Artisan;
use App\Models\ArtisanWallet;
use App\Models\OwnerWallet;
use App\Models\Transaction;
use App\Models\User;
use App\Services\ArtisanWalletService;
use App\Services\OwnerWalletService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

function makeArtisanScenario(): array
{
    $user = User::factory()->create();
    $artisan = Artisan::create([
        'user_id' => $user->id,
        'business_name' => 'Test Artisan',
        'slug' => 'test-artisan-'.Str::random(4),
        'profession' => 'Plombier',
        'phone' => '+33123456789',
        'city' => 'Paris',
        'verified' => true,
        'is_active' => true,
    ]);
    $wallet = ArtisanWallet::create([
        'artisan_id' => $artisan->id,
        'available_balance' => 50000,
        'reserved_balance' => 0,
    ]);

    return compact('user', 'artisan', 'wallet');
}

function fakeProviders(): void
{
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

test('the artisan withdraw form shows the artisan wallet balance', function () {
    $s = makeArtisanScenario();
    $this->actingAs($s['user']);
    fakeProviders();

    $this->get(route('artisan.wallet.withdraw.form'))
        ->assertOk()
        ->assertViewIs('transactions.withdraw-form')
        ->assertSee('50 000');
});

test('the artisan withdraw form is forbidden without an artisan profile', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $this->get(route('artisan.wallet.withdraw.form'))->assertForbidden();
});

test('the artisan withdraw form redirects when the artisan balance is below the minimum', function () {
    $s = makeArtisanScenario();
    $s['wallet']->update(['available_balance' => 500]);
    $this->actingAs($s['user']);

    $this->get(route('artisan.wallet.withdraw.form'))
        ->assertRedirect(route('artisan.wallet'));
});

test('an artisan payout is reserved on the artisan wallet, not the owner wallet', function () {
    $s = makeArtisanScenario();
    $ownerWallet = OwnerWallet::create([
        'owner_id' => $s['user']->id,
        'available_balance' => 999999,
        'reserved_balance' => 0,
    ]);
    $this->actingAs($s['user']);

    Http::fake(['*/v2/payouts' => Http::response([
        'payoutId' => Str::uuid()->toString(),
        'status' => 'ACCEPTED',
        'created' => now()->toIso8601String(),
    ], 200)]);

    $response = $this->post(route('artisan.wallet.withdraw'), [
        '_token' => csrf_token(),
        'amount' => 10000,
        'phone' => '064567890',
        'provider' => 'MTN_MOMO_COG',
    ]);

    $response->assertSessionHasNoErrors();

    $transaction = Transaction::first();

    expect($transaction)->not->toBeNull()
        ->and($transaction->type)->toBe(TransactionType::PAYOUT)
        ->and($transaction->amount)->toBe(10000);

    $s['wallet']->refresh();
    $ownerWallet->refresh();

    expect($s['wallet']->available_balance)->toBe(40000)
        ->and($s['wallet']->reserved_balance)->toBe(10000)
        ->and($ownerWallet->available_balance)->toBe(999999)
        ->and($ownerWallet->reserved_balance)->toBe(0)
        ->and(app(ArtisanWalletService::class)->hasPayoutReservation($transaction))->toBeTrue();
});

test('a completed artisan payout debits the artisan wallet', function () {
    $s = makeArtisanScenario();
    $this->actingAs($s['user']);

    $service = app(ArtisanWalletService::class);
    $payoutId = Str::uuid()->toString();

    $transaction = Transaction::create([
        'transaction_id' => Str::uuid()->toString(),
        'user_id' => $s['user']->id,
        'type' => TransactionType::PAYOUT,
        'status' => TransactionStatus::PENDING,
        '_token' => csrf_token(),
        'amount' => 10000,
        'currency' => 'XAF',
        'payout_id' => $payoutId,
        'provider' => 'MTN_MOMO_COG',
    ]);

    Http::fake(['*/v2/payouts' => Http::response([
        'payoutId' => Str::uuid()->toString(),
        'status' => 'ACCEPTED',
    ], 200)]);

    $service->reservePayout($transaction);

    Http::fake(["*/v2/payouts/{$payoutId}" => Http::response([
        'status' => 'FOUND',
        'data' => [
            'payoutId' => $payoutId,
            'status' => 'COMPLETED',
            'amount' => '10000',
            'currency' => 'XAF',
        ],
    ], 200)]);

    $this->get(route('transactions.withdraw.status', $transaction));

    $s['wallet']->refresh();

    expect($transaction->refresh()->status)->toBe(TransactionStatus::COMPLETED)
        ->and($s['wallet']->available_balance)->toBe(40000)
        ->and($s['wallet']->reserved_balance)->toBe(0);
});

test('a failed artisan payout releases the reservation', function () {
    $s = makeArtisanScenario();
    $this->actingAs($s['user']);

    $service = app(ArtisanWalletService::class);
    $payoutId = Str::uuid()->toString();

    $transaction = Transaction::create([
        'transaction_id' => Str::uuid()->toString(),
        'user_id' => $s['user']->id,
        'type' => TransactionType::PAYOUT,
        'status' => TransactionStatus::PENDING,
        '_token' => csrf_token(),
        'amount' => 10000,
        'currency' => 'XAF',
        'payout_id' => $payoutId,
        'provider' => 'MTN_MOMO_COG',
    ]);

    Http::fake(['*/v2/payouts' => Http::response([
        'payoutId' => Str::uuid()->toString(),
        'status' => 'ACCEPTED',
    ], 200)]);

    $service->reservePayout($transaction);

    Http::fake(["*/v2/payouts/{$payoutId}" => Http::response([
        'status' => 'FOUND',
        'data' => [
            'payoutId' => $payoutId,
            'status' => 'FAILED',
            'failureReason' => [
                'failureCode' => 'INSUFFICIENT_FUNDS',
                'failureMessage' => 'Not enough funds',
            ],
        ],
    ], 200)]);

    $this->get(route('transactions.withdraw.status', $transaction));

    $s['wallet']->refresh();

    expect($transaction->refresh()->status)->toBe(TransactionStatus::FAILED)
        ->and($s['wallet']->available_balance)->toBe(50000)
        ->and($s['wallet']->reserved_balance)->toBe(0);
});

test('an owner payout still settles on the owner wallet', function () {
    $user = User::factory()->create();
    $ownerWallet = OwnerWallet::create([
        'owner_id' => $user->id,
        'available_balance' => 50000,
        'reserved_balance' => 0,
    ]);
    $this->actingAs($user);

    $payoutId = Str::uuid()->toString();

    $transaction = Transaction::create([
        'transaction_id' => Str::uuid()->toString(),
        'user_id' => $user->id,
        'type' => TransactionType::PAYOUT,
        'status' => TransactionStatus::PENDING,
        '_token' => csrf_token(),
        'amount' => 10000,
        'currency' => 'XAF',
        'payout_id' => $payoutId,
        'provider' => 'MTN_MOMO_COG',
    ]);

    app(OwnerWalletService::class)->reservePayout($transaction);

    Http::fake(["*/v2/payouts/{$payoutId}" => Http::response([
        'status' => 'FOUND',
        'data' => [
            'payoutId' => $payoutId,
            'status' => 'COMPLETED',
            'amount' => '10000',
            'currency' => 'XAF',
        ],
    ], 200)]);

    $this->get(route('transactions.withdraw.status', $transaction));

    $ownerWallet->refresh();

    expect($transaction->refresh()->status)->toBe(TransactionStatus::COMPLETED)
        ->and($ownerWallet->available_balance)->toBe(40000)
        ->and($ownerWallet->reserved_balance)->toBe(0);
});
