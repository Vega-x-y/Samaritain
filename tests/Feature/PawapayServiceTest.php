<?php

use App\DataTransferObjects\Pawapay\DepositRequest;
use App\DataTransferObjects\Pawapay\PayoutRequest;
use App\DataTransferObjects\Pawapay\RefundRequest;
use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use App\Exceptions\PawaPayException;
use App\Models\Transaction;
use App\Models\User;
use App\Services\PawapayService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

beforeEach(function () {
    $this->service = new PawapayService;
    $this->baseUrl = config('pawapay.base_url');
});

test('initiateDeposit sends correct payload to PawaPay', function () {
    Http::fake([
        "{$this->baseUrl}/v2/deposits" => Http::response([
            'depositId' => 'test-deposit-id',
            'status' => 'ACCEPTED',
            'created' => now()->toIso8601String(),
        ], 200),
    ]);

    $request = new DepositRequest(
        depositId: 'test-deposit-id',
        phoneNumber: '260763456789',
        provider: 'MTN_MOMO_ZMB',
        amount: '15',
        currency: 'ZMW',
        clientReferenceId: 'INV-123',
    );

    $response = $this->service->initiateDeposit($request);

    expect($response)->toHaveKey('depositId')
        ->and($response['depositId'])->toBe('test-deposit-id')
        ->and($response['status'])->toBe('ACCEPTED');

    Http::assertSent(function ($request) {
        $body = $request->data();

        return $request->url() === "{$this->baseUrl}/v2/deposits"
            && $body['depositId'] === 'test-deposit-id'
            && $body['payer']['accountDetails']['phoneNumber'] === '260763456789'
            && $body['payer']['accountDetails']['provider'] === 'MTN_MOMO_ZMB'
            && $body['amount'] === '15'
            && $body['currency'] === 'ZMW'
            && $body['clientReferenceId'] === 'INV-123';
    });
});

test('initiateDeposit throws exception on API failure', function () {
    Http::fake([
        "{$this->baseUrl}/v2/deposits" => Http::response([
            'error' => 'Invalid request',
        ], 400),
    ]);

    $request = new DepositRequest(
        depositId: 'test-deposit-id',
        phoneNumber: '260763456789',
        provider: 'MTN_MOMO_ZMB',
        amount: '15',
        currency: 'ZMW',
    );

    $this->service->initiateDeposit($request);
})->throws(PawaPayException::class, 'Erreur lors de l\'initiation du dépôt PawaPay.');

test('getDepositStatus retrieves deposit status', function () {
    Http::fake([
        "{$this->baseUrl}/v2/deposits/test-deposit-id" => Http::response([
            'status' => 'FOUND',
            'data' => [
                'depositId' => 'test-deposit-id',
                'status' => 'COMPLETED',
                'amount' => '15',
            ],
        ], 200),
    ]);

    $response = $this->service->getDepositStatus('test-deposit-id');

    expect($response)->toHaveKey('status')
        ->and($response['status'])->toBe('COMPLETED')
        ->and($response['data']['status'])->toBe('COMPLETED');
});

test('initiateBulkPayout sends correct payload to PawaPay', function () {
    Http::fake([
        "{$this->baseUrl}/v2/payouts/bulk" => Http::response([
            [
                'payoutId' => 'payout-1',
                'status' => 'ACCEPTED',
            ],
            [
                'payoutId' => 'payout-2',
                'status' => 'ACCEPTED',
            ],
        ], 200),
    ]);

    $payouts = [
        new PayoutRequest(
            payoutId: 'payout-1',
            phoneNumber: '260763456789',
            provider: 'MTN_MOMO_ZMB',
            amount: '10',
            currency: 'ZMW',
        ),
        new PayoutRequest(
            payoutId: 'payout-2',
            phoneNumber: '260763456790',
            provider: 'MTN_MOMO_ZMB',
            amount: '20',
            currency: 'ZMW',
        ),
    ];

    $response = $this->service->initiateBulkPayout($payouts);

    expect($response)->toBeArray()
        ->and($response)->toHaveCount(2)
        ->and($response[0]['payoutId'])->toBe('payout-1')
        ->and($response[1]['payoutId'])->toBe('payout-2');

    Http::assertSent(function ($request) {
        $body = $request->data();

        return $request->url() === "{$this->baseUrl}/v2/payouts/bulk"
            && is_array($body)
            && count($body) === 2
            && $body[0]['payoutId'] === 'payout-1'
            && $body[1]['payoutId'] === 'payout-2';
    });
});

test('initiatePayout sends correct payload to PawaPay', function () {
    Http::fake([
        "{$this->baseUrl}/v2/payouts" => Http::response([
            'payoutId' => 'test-payout-id',
            'status' => 'ACCEPTED',
            'created' => now()->toIso8601String(),
        ], 200),
    ]);

    $request = new PayoutRequest(
        payoutId: 'test-payout-id',
        phoneNumber: '260763456789',
        provider: 'MTN_MOMO_ZMB',
        amount: '15',
        currency: 'ZMW',
    );

    $response = $this->service->initiatePayout($request);

    expect($response)->toHaveKey('payoutId')
        ->and($response['payoutId'])->toBe('test-payout-id')
        ->and($response['status'])->toBe('ACCEPTED');

    Http::assertSent(function ($request) {
        $body = $request->data();

        return $request->url() === "{$this->baseUrl}/v2/payouts"
            && $body['payoutId'] === 'test-payout-id'
            && $body['recipient']['accountDetails']['phoneNumber'] === '260763456789'
            && $body['amount'] === '15';
    });
});

test('getPayoutStatus retrieves payout status', function () {
    Http::fake([
        "{$this->baseUrl}/v2/payouts/test-payout-id" => Http::response([
            'status' => 'FOUND',
            'data' => [
                'payoutId' => 'test-payout-id',
                'status' => 'COMPLETED',
                'amount' => '15',
            ],
        ], 200),
    ]);

    $response = $this->service->getPayoutStatus('test-payout-id');

    expect($response)->toHaveKey('status')
        ->and($response['status'])->toBe('COMPLETED');
});

test('cancelPayout cancels an enqueued payout', function () {
    Http::fake([
        "{$this->baseUrl}/v2/payouts/test-payout-id/cancel" => Http::response([
            'payoutId' => 'test-payout-id',
            'status' => 'CANCELLED',
        ], 200),
    ]);

    $response = $this->service->cancelPayout('test-payout-id');

    expect($response)->toHaveKey('status')
        ->and($response['status'])->toBe('CANCELLED');
});

test('initiateRefund sends correct payload to PawaPay', function () {
    Http::fake([
        "{$this->baseUrl}/v2/refunds" => Http::response([
            'refundId' => 'test-refund-id',
            'depositId' => 'test-deposit-id',
            'status' => 'ACCEPTED',
        ], 200),
    ]);

    $request = new RefundRequest(
        refundId: 'test-refund-id',
        depositId: 'test-deposit-id',
        amount: '15',
    );

    $response = $this->service->initiateRefund($request);

    expect($response)->toHaveKey('refundId')
        ->and($response['refundId'])->toBe('test-refund-id')
        ->and($response['status'])->toBe('ACCEPTED');

    Http::assertSent(function ($request) {
        $body = $request->data();

        return $request->url() === "{$this->baseUrl}/v2/refunds"
            && $body['refundId'] === 'test-refund-id'
            && $body['depositId'] === 'test-deposit-id'
            && $body['amount'] === '15';
    });
});

test('getRefundStatus retrieves refund status', function () {
    Http::fake([
        "{$this->baseUrl}/v2/refunds/test-refund-id" => Http::response([
            'status' => 'FOUND',
            'data' => [
                'refundId' => 'test-refund-id',
                'status' => 'COMPLETED',
            ],
        ], 200),
    ]);

    $response = $this->service->getRefundStatus('test-refund-id');

    expect($response)->toHaveKey('status')
        ->and($response['status'])->toBe('COMPLETED');
});

test('createPaymentPage creates hosted payment page', function () {
    Http::fake([
        "{$this->baseUrl}/v2/paymentpage" => Http::response([
            'redirectUrl' => 'https://payment.pawapay.io/xyz',
            'depositId' => 'test-deposit-id',
        ], 200),
    ]);

    $response = $this->service->createPaymentPage(
        depositId: 'test-deposit-id',
        returnUrl: 'https://example.com/return',
        amount: '15',
        currency: 'ZMW',
    );

    expect($response)->toHaveKey('redirectUrl')
        ->and($response['redirectUrl'])->toBe('https://payment.pawapay.io/xyz');

    Http::assertSent(fn ($request) => $request->url() === "{$this->baseUrl}/v2/paymentpage"
        && $request['depositId'] === 'test-deposit-id'
        && $request['returnUrl'] === 'https://example.com/return'
        && $request['amountDetails']['amount'] === '15'
        && $request['amountDetails']['currency'] === 'ZMW');
});

test('createPaymentPage throws exception when redirectUrl is missing', function () {
    Http::fake([
        "{$this->baseUrl}/v2/paymentpage" => Http::response([
            'depositId' => 'test-deposit-id',
            // Missing redirectUrl
        ], 200),
    ]);

    $this->service->createPaymentPage(
        depositId: 'test-deposit-id',
        returnUrl: 'https://example.com/return',
        amount: '15',
        currency: 'ZMW',
    );
})->throws(PawaPayException::class, 'PawaPay n\'a pas fourni de lien de redirection.');

test('predictProvider predicts mobile money provider', function () {
    Http::fake([
        "{$this->baseUrl}/v2/toolkit/predict-provider" => Http::response([
            'provider' => 'MTN_MOMO_ZMB',
            'phoneNumber' => '260763456789',
            'country' => 'ZMB',
        ], 200),
    ]);

    $response = $this->service->predictProvider('260763456789');

    expect($response)->toHaveKey('provider')
        ->and($response['provider'])->toBe('MTN_MOMO_ZMB')
        ->and($response['phoneNumber'])->toBe('260763456789')
        ->and($response['country'])->toBe('ZMB');
});

test('getActiveConfiguration retrieves active configuration', function () {
    Http::fake([
        "{$this->baseUrl}/v2/toolkit/active-configuration" => Http::response([
            'correspondents' => [
                [
                    'correspondent' => 'MTN_MOMO_ZMB',
                    'currencies' => ['ZMW'],
                    'decimalsInAmount' => 'NONE',
                ],
            ],
        ], 200),
    ]);

    $response = $this->service->getActiveConfiguration();

    expect($response)->toHaveKey('correspondents')
        ->and($response['correspondents'])->toBeArray();
});

test('getAvailability retrieves provider availability', function () {
    Http::fake([
        "{$this->baseUrl}/v2/toolkit/availability" => Http::response([
            'correspondents' => [
                ['correspondent' => 'MTN_MOMO_ZMB', 'available' => true],
            ],
        ], 200),
    ]);

    $response = $this->service->getAvailability();

    expect($response)->toHaveKey('correspondents');
});

test('normalizePhoneNumber removes non-digit characters', function () {
    $normalized = $this->service->normalizePhoneNumber('+260 76 345 6789');

    expect($normalized)->toBe('260763456789');
});

test('normalizePhoneNumber removes leading plus', function () {
    $normalized = $this->service->normalizePhoneNumber('+260763456789');

    expect($normalized)->toBe('260763456789');
});

test('handleCallback updates transaction status for deposit', function () {
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
        'provider' => 'MTN_MOMO_ZMB',
    ]);

    $payload = [
        'depositId' => $depositId,
        'status' => 'COMPLETED',
        'requestedAmount' => '15',
        'depositedAmount' => '15',
        'currency' => 'ZMW',
    ];

    $result = $this->service->handleCallback($payload);

    expect($result)->toBeInstanceOf(Transaction::class)
        ->and($result->status)->toBe(TransactionStatus::COMPLETED)
        ->and($result->raw_response)->toHaveKey('depositId');

    // Verify database was updated
    $transaction->refresh();
    expect($transaction->status)->toBe(TransactionStatus::COMPLETED);
});

test('handleCallback updates transaction status for payout', function () {
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

    $result = $this->service->handleCallback($payload);

    expect($result)->toBeInstanceOf(Transaction::class)
        ->and($result->status)->toBe(TransactionStatus::COMPLETED);
});

test('handleCallback is idempotent', function () {
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

    // First callback
    $this->service->handleCallback($payload);

    $transaction->refresh();
    $updatedAt1 = $transaction->updated_at;

    // Second callback (duplicate)
    sleep(1);
    $this->service->handleCallback($payload);

    $transaction->refresh();
    $updatedAt2 = $transaction->updated_at;

    // Status should still be COMPLETED, but updated_at should NOT change
    // because the status hasn't changed
    expect($transaction->status)->toBe(TransactionStatus::COMPLETED)
        ->and($updatedAt1->eq($updatedAt2))->toBeTrue();
});

test('handleCallback returns null for unknown transaction', function () {
    $payload = [
        'depositId' => 'unknown-deposit-id',
        'status' => 'COMPLETED',
    ];

    $result = $this->service->handleCallback($payload);

    expect($result)->toBeNull();
});

test('resendDepositCallback triggers resend', function () {
    Http::fake([
        "{$this->baseUrl}/v2/deposits/test-deposit-id/resend-callback" => Http::response([
            'success' => true,
        ], 200),
    ]);

    $response = $this->service->resendDepositCallback('test-deposit-id');

    expect($response)->toHaveKey('success');
});

test('resendPayoutCallback triggers resend', function () {
    Http::fake([
        "{$this->baseUrl}/v2/payouts/test-payout-id/resend-callback" => Http::response([
            'success' => true,
        ], 200),
    ]);

    $response = $this->service->resendPayoutCallback('test-payout-id');

    expect($response)->toHaveKey('success');
});

test('resendRefundCallback triggers resend', function () {
    Http::fake([
        "{$this->baseUrl}/v2/refunds/test-refund-id/resend-callback" => Http::response([
            'success' => true,
        ], 200),
    ]);

    $response = $this->service->resendRefundCallback('test-refund-id');

    expect($response)->toHaveKey('success');
});
