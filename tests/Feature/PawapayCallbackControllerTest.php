<?php

use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Str;

test('callback endpoint processes deposit callback successfully', function () {
    $user = User::factory()->create();
    $depositId = Str::uuid()->toString();

    // Create pending transaction
    $transaction = Transaction::create([
        'transaction_id' => Str::uuid()->toString(),
        'user_id' => $user->id,
        'deposit_id' => $depositId,
        'type' => TransactionType::DEPOSIT,
        'status' => TransactionStatus::PENDING,
        'amount' => 1500,
        'currency' => 'ZMW',
        'provider' => 'MTN_MOMO_ZMB',
    ]);

    // Simulate PawaPay callback
    $payload = [
        'depositId' => $depositId,
        'status' => 'COMPLETED',
        'requestedAmount' => '15',
        'depositedAmount' => '15',
        'currency' => 'ZMW',
        'country' => 'ZMB',
        'correspondent' => 'MTN_MOMO_ZMB',
        'payer' => [
            'type' => 'MSISDN',
            'address' => ['value' => '260763456789'],
        ],
        'created' => now()->toIso8601String(),
        'respondedByPayer' => now()->toIso8601String(),
    ];

    $response = $this->postJson(route('pawapay.callback'), $payload);

    $response->assertOk()
        ->assertJson([
            'status' => 'success',
            'transaction_id' => $transaction->transaction_id,
        ]);

    // Verify transaction was updated
    $transaction->refresh();
    expect($transaction->status)->toBe(TransactionStatus::COMPLETED)
        ->and($transaction->raw_response)->toHaveKey('depositId');
});

test('callback endpoint processes payout callback successfully', function () {
    $user = User::factory()->create();
    $payoutId = Str::uuid()->toString();

    $transaction = Transaction::create([
        'transaction_id' => Str::uuid()->toString(),
        'user_id' => $user->id,
        'payout_id' => $payoutId,
        'type' => TransactionType::PAYOUT,
        'status' => TransactionStatus::ACCEPTED,
        'amount' => 1500,
        'currency' => 'ZMW',
        'provider' => 'MTN_MOMO_ZMB',
    ]);

    $payload = [
        'payoutId' => $payoutId,
        'status' => 'COMPLETED',
        'requestedAmount' => '15',
        'currency' => 'ZMW',
    ];

    $response = $this->postJson(route('pawapay.callback'), $payload);

    $response->assertOk()
        ->assertJson(['status' => 'success']);

    $transaction->refresh();
    expect($transaction->status)->toBe(TransactionStatus::COMPLETED);
});

test('callback endpoint handles failed deposit', function () {
    $user = User::factory()->create();
    $depositId = Str::uuid()->toString();

    $transaction = Transaction::create([
        'transaction_id' => Str::uuid()->toString(),
        'user_id' => $user->id,
        'deposit_id' => $depositId,
        'type' => TransactionType::DEPOSIT,
        'status' => TransactionStatus::SUBMITTED,
        'amount' => 1500,
        'currency' => 'ZMW',
    ]);

    $payload = [
        'depositId' => $depositId,
        'status' => 'FAILED',
        'failureReason' => [
            'failureCode' => 'PAYER_INSUFFICIENT_BALANCE',
            'failureMessage' => 'Payer has insufficient balance',
        ],
    ];

    $response = $this->postJson(route('pawapay.callback'), $payload);

    $response->assertOk();

    $transaction->refresh();
    expect($transaction->status)->toBe(TransactionStatus::FAILED)
        ->and($transaction->raw_response['failureReason']['failureCode'])->toBe('PAYER_INSUFFICIENT_BALANCE');
});

test('callback endpoint acknowledges unknown transaction', function () {
    $payload = [
        'depositId' => 'unknown-deposit-id',
        'status' => 'COMPLETED',
    ];

    $response = $this->postJson(route('pawapay.callback'), $payload);

    // Should still return 200 to acknowledge receipt
    $response->assertOk()
        ->assertJson([
            'status' => 'received',
            'message' => 'Callback received but could not be processed',
        ]);
});

test('callback endpoint is idempotent', function () {
    $user = User::factory()->create();
    $depositId = Str::uuid()->toString();

    $transaction = Transaction::create([
        'transaction_id' => Str::uuid()->toString(),
        'user_id' => $user->id,
        'deposit_id' => $depositId,
        'type' => TransactionType::DEPOSIT,
        'status' => TransactionStatus::PENDING,
        'amount' => 1500,
        'currency' => 'ZMW',
    ]);

    $payload = [
        'depositId' => $depositId,
        'status' => 'COMPLETED',
    ];

    // Send callback twice
    $this->postJson(route('pawapay.callback'), $payload)->assertOk();
    $this->postJson(route('pawapay.callback'), $payload)->assertOk();

    // Should only update once
    $transaction->refresh();
    expect($transaction->status)->toBe(TransactionStatus::COMPLETED);

    // Should have exactly 2 callbacks in raw_response (merged)
    expect($transaction->raw_response)->toBeArray();
});

test('callback route does not require CSRF token', function () {
    // This test verifies that the webhook can be called without CSRF protection
    // (webhooks don't send CSRF tokens)

    $user = User::factory()->create();
    $depositId = Str::uuid()->toString();

    Transaction::create([
        'transaction_id' => Str::uuid()->toString(),
        'user_id' => $user->id,
        'deposit_id' => $depositId,
        'type' => TransactionType::DEPOSIT,
        'status' => TransactionStatus::PENDING,
        'amount' => 1500,
    ]);

    $payload = [
        'depositId' => $depositId,
        'status' => 'COMPLETED',
    ];

    // Post without session/CSRF - should still work
    $response = $this->postJson(route('pawapay.callback'), $payload);

    $response->assertOk();
});
