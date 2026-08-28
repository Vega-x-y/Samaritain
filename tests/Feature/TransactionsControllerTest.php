<?php

use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use App\Models\Contract;
use App\Models\OwnerWallet;
use App\Models\Property;
use App\Models\RentPayment;
use App\Models\Transaction;
use App\Models\User;
use App\Models\VisitPass;
use App\Services\OwnerWalletService;
use App\Services\VisitPassService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
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

test('the deposit form is pre-filled for a visit pass context', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $visitPass = VisitPass::create([
        'uuid' => Str::uuid()->toString(),
        'user_id' => $user->id,
        'amount' => 5000,
        'payment_status' => 'pending',
        'status' => 'pending_payment',
    ]);

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

    $this->get(route('transactions.deposit', ['visit_pass' => $visitPass->uuid]))
        ->assertOk()
        ->assertSee('Pass visite')
        ->assertSee($visitPass->reference);
});

test('an already paid visit pass cannot be paid again', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $visitPass = VisitPass::create([
        'uuid' => Str::uuid()->toString(),
        'user_id' => $user->id,
        'amount' => 5000,
        'payment_status' => 'paid',
        'status' => 'active',
        'paid_at' => now(),
    ]);

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

    $this->get(route('transactions.deposit', ['visit_pass' => $visitPass->uuid]))
        ->assertRedirect(route('my-visit-passes.show', $visitPass));
});

test('init deposit for a visit pass uses the pass amount and links it', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $visitPass = VisitPass::create([
        'uuid' => Str::uuid()->toString(),
        'user_id' => $user->id,
        'amount' => 9000,
        'payment_status' => 'pending',
        'status' => 'pending_payment',
    ]);

    Http::fake([
        '*/v2/deposits' => Http::response([
            'depositId' => Str::uuid()->toString(),
            'status' => 'ACCEPTED',
            'created' => now()->toIso8601String(),
        ], 200),
    ]);

    $this->post(route('transactions.deposit'), [
        'amount' => 100,
        'phone' => '064567890',
        'provider' => 'MTN_MOMO_COG',
        'visit_pass' => $visitPass->uuid,
    ]);

    $transaction = Transaction::first();
    expect($transaction)->not->toBeNull()
        ->and($transaction->visit_pass_id)->toBe($visitPass->id)
        ->and($transaction->amount)->toBe(9000);
});

test('a completed visit pass deposit marks the pass paid without crediting the wallet', function () {
    $user = User::factory()->create();
    $wallet = makeWallet($user);
    $this->actingAs($user);

    $visitPass = VisitPass::create([
        'uuid' => Str::uuid()->toString(),
        'user_id' => $user->id,
        'amount' => 9000,
        'payment_status' => 'pending',
        'status' => 'pending_payment',
    ]);

    $depositId = Str::uuid()->toString();

    $transaction = Transaction::create([
        'transaction_id' => Str::uuid()->toString(),
        'user_id' => $user->id,
        'visit_pass_id' => $visitPass->id,
        'type' => TransactionType::DEPOSIT,
        'status' => TransactionStatus::PENDING,
        'amount' => 9000,
        'currency' => 'XAF',
        'deposit_id' => $depositId,
        'provider' => 'MTN_MOMO_COG',
    ]);

    $visitPassService = Mockery::mock(VisitPassService::class);
    $visitPassService->shouldReceive('handleSuccessfulPayment')->once();
    app()->instance(VisitPassService::class, $visitPassService);

    Http::fake([
        "*/v2/deposits/{$depositId}" => Http::response([
            'status' => 'FOUND',
            'data' => [
                'depositId' => $depositId,
                'status' => 'COMPLETED',
                'amount' => '90.00',
                'currency' => 'XAF',
            ],
        ], 200),
    ]);

    $this->get(route('transactions.deposit.status', $transaction));

    $wallet->refresh();

    expect($transaction->refresh()->status)->toBe(TransactionStatus::COMPLETED)
        ->and($wallet->available_balance)->toBe(0);
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

function makeRentScenario(): array
{
    $owner = User::factory()->create();
    $tenant = User::factory()->create();
    $property = Property::factory()->create(['created_by' => $owner->id]);
    $contract = Contract::factory()->create([
        'property_id' => $property->id,
        'tenant_email' => $tenant->email,
        'status' => 'active',
        'created_by' => $owner->id,
    ]);
    $rentPayment = RentPayment::factory()->create([
        'contract_id' => $contract->id,
        'amount_due' => 150000,
        'amount_paid' => 0,
        'status' => 'unpaid',
    ]);

    return compact('owner', 'tenant', 'property', 'contract', 'rentPayment');
}

test('deposit form is prefilled for a rent payment', function () {
    $s = makeRentScenario();
    $this->actingAs($s['tenant']);

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

    $this->get(route('transactions.deposit', ['rent_payment' => $s['rentPayment']->id]))
        ->assertOk()
        ->assertViewHas('rentPayment', fn ($rentPayment) => $rentPayment->is($s['rentPayment']))
        ->assertSee('Loyer');
});

test('init deposit for a rent payment uses the rent amount and links it', function () {
    $s = makeRentScenario();
    $this->actingAs($s['tenant']);

    Http::fake([
        '*/v2/deposits' => Http::response([
            'depositId' => Str::uuid()->toString(),
            'status' => 'ACCEPTED',
            'created' => now()->toIso8601String(),
        ], 200),
    ]);

    $this->post(route('transactions.deposit'), [
        'amount' => 999, // ignored: the server-side amount_due is authoritative
        'phone' => '064567890',
        'provider' => 'MTN_MOMO_COG',
        'rent_payment' => $s['rentPayment']->id,
    ]);

    $transaction = Transaction::first();

    expect($transaction)->not->toBeNull()
        ->and($transaction->rent_payment_id)->toBe($s['rentPayment']->id)
        ->and($transaction->amount)->toBe(150000);
});

test('a completed rent deposit marks the rent paid, credits the owner wallet and not the tenant wallet', function () {
    $s = makeRentScenario();
    $tenantWallet = makeWallet($s['tenant']);
    $ownerWallet = makeWallet($s['owner']);
    $this->actingAs($s['tenant']);

    $depositId = Str::uuid()->toString();

    $transaction = Transaction::create([
        'transaction_id' => Str::uuid()->toString(),
        'user_id' => $s['tenant']->id,
        'rent_payment_id' => $s['rentPayment']->id,
        'type' => TransactionType::DEPOSIT,
        'status' => TransactionStatus::PENDING,
        'amount' => 150000,
        'currency' => 'XAF',
        'deposit_id' => $depositId,
        'provider' => 'MTN_MOMO_COG',
    ]);

    // RentPaymentService runs for real: it marks the rent as paid and
    // generates a PDF receipt (stubbed here to keep the test focused on
    // the wallet routing).
    config(['filesystems.default' => 'local']);
    Storage::fake('local');
    Pdf::shouldReceive('loadView')->once()->andReturnSelf();
    Pdf::shouldReceive('output')->once()->andReturn('fake-pdf');

    Http::fake([
        "*/v2/deposits/{$depositId}" => Http::response([
            'status' => 'FOUND',
            'data' => [
                'depositId' => $depositId,
                'status' => 'COMPLETED',
                'amount' => '1500.00',
                'currency' => 'XAF',
            ],
        ], 200),
    ]);

    $this->get(route('transactions.deposit.status', $transaction));

    $tenantWallet->refresh();
    $ownerWallet->refresh();

    expect($transaction->refresh()->status)->toBe(TransactionStatus::COMPLETED)
        ->and($s['rentPayment']->refresh()->status)->toBe('paid')
        ->and($ownerWallet->available_balance)->toBe(150000)
        ->and($tenantWallet->available_balance)->toBe(0);
});

test('a tenant cannot open the deposit form for another tenant rent payment', function () {
    $s = makeRentScenario();
    $otherTenant = User::factory()->create();
    $this->actingAs($otherTenant);

    $this->get(route('transactions.deposit', ['rent_payment' => $s['rentPayment']->id]))
        ->assertRedirect(route('tenant.payments'));

    expect(Transaction::count())->toBe(0);
});
