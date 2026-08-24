<?php

use App\DataTransferObjects\Pawapay\DepositRequest;
use App\DataTransferObjects\Pawapay\PayoutRequest;
use App\DataTransferObjects\Pawapay\RefundRequest;
use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use App\Models\Transaction;
use App\Models\User;
use App\Services\PawapayService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

test('can initiate a deposit with PawaPay', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    // Mock HTTP response
    Http::fake([
        '*/v2/deposits' => Http::response([
            'depositId' => $depositId = Str::uuid()->toString(),
            'status' => 'ACCEPTED',
            'created' => now()->toIso8601String(),
        ], 200),
    ]);

    $service = app(PawapayService::class);

    $request = new DepositRequest(
        depositId: $depositId,
        phoneNumber: '242064567890',
        provider: 'MTN_MOMO_COG',
        amount: '100',
        currency: 'XAF'
    );

    $response = $service->initiateDeposit($request);

    expect($response)->toHaveKey('depositId')
        ->and($response['status'])->toBe('ACCEPTED');
});

test('can check deposit status', function () {
    $depositId = Str::uuid()->toString();

    Http::fake([
        "*/v2/deposits/{$depositId}" => Http::response([
            'status' => 'FOUND',
            'data' => [
                'depositId' => $depositId,
                'status' => 'COMPLETED',
                'amount' => '100',
                'currency' => 'XAF',
            ],
        ], 200),
    ]);

    $service = app(PawapayService::class);
    $response = $service->getDepositStatus($depositId);

    expect($response['status'])->toBe('FOUND')
        ->and($response['data']['status'])->toBe('COMPLETED');
});

test('can initiate a payout', function () {
    $payoutId = Str::uuid()->toString();

    Http::fake([
        '*/v2/payouts' => Http::response([
            'payoutId' => $payoutId,
            'status' => 'ACCEPTED',
            'created' => now()->toIso8601String(),
        ], 200),
    ]);

    $service = app(PawapayService::class);

    $request = new PayoutRequest(
        payoutId: $payoutId,
        phoneNumber: '242064567890',
        provider: 'MTN_MOMO_COG',
        amount: '50',
        currency: 'XAF'
    );

    $response = $service->initiatePayout($request);

    expect($response)->toHaveKey('payoutId')
        ->and($response['status'])->toBe('ACCEPTED');
});

test('can initiate a refund', function () {
    $depositId = Str::uuid()->toString();
    $refundId = Str::uuid()->toString();

    Http::fake([
        '*/v2/refunds' => Http::response([
            'refundId' => $refundId,
            'depositId' => $depositId,
            'status' => 'ACCEPTED',
        ], 200),
    ]);

    $service = app(PawapayService::class);

    $request = new RefundRequest(
        refundId: $refundId,
        depositId: $depositId,
        amount: '50'
    );

    $response = $service->initiateRefund($request);

    expect($response['status'])->toBe('ACCEPTED')
        ->and($response['depositId'])->toBe($depositId);
});

test('can predict provider from phone number', function () {
    $phoneNumber = '242064567890';

    Http::fake([
        '*/v2/toolkit/predict-provider' => Http::response([
            'provider' => 'MTN_MOMO_COG',
            'phoneNumber' => $phoneNumber,
            'country' => 'COG',
        ], 200),
    ]);

    $service = app(PawapayService::class);
    $response = $service->predictProvider($phoneNumber);

    expect($response['provider'])->toBe('MTN_MOMO_COG')
        ->and($response['country'])->toBe('COG');
});

test('can handle callback and update transaction', function () {
    $user = User::factory()->create();
    $depositId = Str::uuid()->toString();

    // Create a pending transaction
    $transaction = Transaction::create([
        'transaction_id' => $depositId,
        'user_id' => $user->id,
        'type' => TransactionType::DEPOSIT,
        'status' => TransactionStatus::PENDING,
        'amount' => 10000,
        'deposit_id' => $depositId,
        'provider' => 'MTN_MOMO_COG',
        'currency' => 'XAF',
        'raw_response' => [],
    ]);

    expect($transaction->status)->toBe(TransactionStatus::PENDING);

    // Simulate callback payload
    $payload = [
        'depositId' => $depositId,
        'status' => 'COMPLETED',
        'requestedAmount' => '100',
        'depositedAmount' => '100',
        'currency' => 'XAF',
        'country' => 'COG',
        'correspondent' => 'MTN_MOMO_COG',
    ];

    $service = app(PawapayService::class);
    $updatedTransaction = $service->handleCallback($payload);

    expect($updatedTransaction)->not->toBeNull()
        ->and($updatedTransaction->status)->toBe(TransactionStatus::COMPLETED)
        ->and($updatedTransaction->transaction_id)->toBe($depositId);
});

test('normalizes phone numbers correctly', function () {
    $service = app(PawapayService::class);

    expect($service->normalizePhoneNumber('+242 064 567 890'))->toBe('242064567890')
        ->and($service->normalizePhoneNumber('242-064-567-890'))->toBe('242064567890')
        ->and($service->normalizePhoneNumber('  242 064 567 890  '))->toBe('242064567890');
});

test('transaction scopes work correctly', function () {
    $user = User::factory()->create();

    // Create transactions of different types
    Transaction::create([
        'transaction_id' => Str::uuid()->toString(),
        'user_id' => $user->id,
        'type' => TransactionType::DEPOSIT,
        'status' => TransactionStatus::COMPLETED,
        'amount' => 10000,
        'currency' => 'XAF',
    ]);

    Transaction::create([
        'transaction_id' => Str::uuid()->toString(),
        'user_id' => $user->id,
        'type' => TransactionType::PAYOUT,
        'status' => TransactionStatus::PENDING,
        'amount' => 5000,
        'currency' => 'XAF',
    ]);

    Transaction::create([
        'transaction_id' => Str::uuid()->toString(),
        'user_id' => $user->id,
        'type' => TransactionType::DEPOSIT,
        'status' => TransactionStatus::FAILED,
        'amount' => 2000,
        'currency' => 'XAF',
    ]);

    expect(Transaction::deposits()->count())->toBe(2)
        ->and(Transaction::payouts()->count())->toBe(1)
        ->and(Transaction::completed()->count())->toBe(1)
        ->and(Transaction::pending()->count())->toBe(1)
        ->and(Transaction::failed()->count())->toBe(1);
});

test('transaction attributes work correctly', function () {
    $transaction = Transaction::create([
        'transaction_id' => Str::uuid()->toString(),
        'user_id' => User::factory()->create()->id,
        'type' => TransactionType::DEPOSIT,
        'status' => TransactionStatus::COMPLETED,
        'amount' => 10000,
        'currency' => 'XAF',
    ]);

    expect($transaction->is_completed)->toBeTrue()
        ->and($transaction->is_pending)->toBeFalse()
        ->and($transaction->is_failed)->toBeFalse();

    $failedTransaction = Transaction::create([
        'transaction_id' => Str::uuid()->toString(),
        'user_id' => $transaction->user_id,
        'type' => TransactionType::DEPOSIT,
        'status' => TransactionStatus::FAILED,
        'amount' => 5000,
        'currency' => 'XAF',
    ]);

    expect($failedTransaction->is_completed)->toBeFalse()
        ->and($failedTransaction->is_failed)->toBeTrue();
});
