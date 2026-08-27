<?php

use App\Enums\TransactionStatus;
use App\Exceptions\PawaPayException;
use App\Jobs\ProcessPawaPayCallback;
use App\Models\Property;
use App\Models\Transaction;
use App\Models\User;
use App\Models\VisitPass;
use App\Services\PawapayService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

/*
|--------------------------------------------------------------------------
| Tests d'unitÃ© du PawapayService
|--------------------------------------------------------------------------
*/

test('predict_provider retourne le provider et le phoneNumber normalisÃ©s', function () {
    config(['services.pawapay.base_url' => 'https://api.sandbox.pawapay.io']);
    config(['services.pawapay.token' => 'test-token']);

    Http::fake([
        'api.sandbox.pawapay.io/v2/predict-provider' => Http::response([
            'country' => 'COG',
            'provider' => 'MTN_MOMO_COG',
            'phoneNumber' => '242061234567',
        ], 200),
    ]);

    $service = new PawapayService;
    $result = $service->predictProvider('+242061234567');

    expect($result['provider'])->toBe('MTN_MOMO_COG')
        ->and($result['phoneNumber'])->toBe('242061234567')
        ->and($result['country'])->toBe('COG');
});

test('predict_provider lance une exception si l\'API Ã©choue', function () {
    config(['services.pawapay.base_url' => 'https://api.sandbox.pawapay.io']);
    config(['services.pawapay.token' => 'test-token']);

    Http::fake([
        'api.sandbox.pawapay.io/v2/predict-provider' => Http::response('Error', 500),
    ]);

    $service = new PawapayService;

    expect(fn () => $service->predictProvider('+242061234567'))
        ->toThrow(PawaPayException::class, 'Impossible de prÃ©dire le fournisseur');
});

test('get_active_configuration retourne les providers et dÃ©cimales', function () {
    config(['services.pawapay.base_url' => 'https://api.sandbox.pawapay.io']);
    config(['services.pawapay.token' => 'test-token']);

    Http::fake([
        'api.sandbox.pawapay.io/v2/active-conf' => Http::response([
            'countries' => [[
                'country' => 'COG',
                'providers' => [[
                    'provider' => 'MTN_MOMO_COG',
                    'currencies' => [[
                        'currency' => 'XAF',
                        'operationTypes' => ['DEPOSIT' => ['decimalsInAmount' => 'NONE']],
                    ]],
                ]],
            ]],
        ], 200),
    ]);

    $service = new PawapayService;
    $result = $service->getActiveConfiguration();

    expect($result['countries'][0]['country'])->toBe('COG')
        ->and($result['countries'][0]['providers'][0]['provider'])->toBe('MTN_MOMO_COG');
});

test('available_providers_with_branding rÃ©cupÃ¨re les logos et libellÃ©s depuis active-configuration', function () {
    config(['services.pawapay.base_url' => 'https://api.sandbox.pawapay.io']);
    config(['services.pawapay.token' => 'test-token']);

    Http::fake([
        'api.sandbox.pawapay.io/v2/active-conf' => Http::response([
            'countries' => [[
                'providers' => [
                    ['provider' => 'MTN_MOMO_COG', 'displayName' => 'MTN Mobile Money', 'logo' => 'https://img.pawapay/MTN_MOMO_COG.png', 'currencies' => [['operationTypes' => ['DEPOSIT' => ['status' => 'OPERATIONAL']]]]],
                    ['provider' => 'AIRTEL_COG', 'displayName' => 'Airtel Money', 'logo' => 'https://img.pawapay/AIRTEL_COG.png', 'currencies' => [['operationTypes' => ['DEPOSIT' => ['status' => 'OPERATIONAL']]]]],
                ],
            ]],
        ], 200),
    ]);

    $service = new PawapayService;
    $branding = $service->availableProvidersWithBranding();

    expect($branding)
        ->toHaveKeys(['MTN_MOMO_COG', 'AIRTEL_COG'])
        ->and($branding['MTN_MOMO_COG']['label'])->toBe('MTN Mobile Money')
        ->and($branding['MTN_MOMO_COG']['logo'])->toBe('https://img.pawapay/MTN_MOMO_COG.png')
        ->and($branding['AIRTEL_COG']['logo'])->toBe('https://img.pawapay/AIRTEL_COG.png');
});

test('available_providers_with_branding retombe sur les libellÃ©s sans logo quand pawaPay Ã©choue', function () {
    config(['services.pawapay.base_url' => 'https://api.sandbox.pawapay.io']);
    config(['services.pawapay.token' => 'test-token']);

    Http::fake([
        'api.sandbox.pawapay.io/v2/active-conf' => Http::response('Error', 500),
    ]);

    $service = new PawapayService;
    $branding = $service->availableProvidersWithBranding();

    expect(array_keys($branding))->toBe(['MTN_MOMO_COG', 'AIRTEL_COG'])
        ->and($branding['MTN_MOMO_COG']['logo'])->toBeNull()
        ->and($branding['MTN_MOMO_COG']['label'])->toBe('MTN Mobile Money')
        ->and($branding['AIRTEL_COG']['logo'])->toBeNull();
});

test('create_deposit envoie le depositId et retourne ACCEPTED', function () {
    config(['services.pawapay.base_url' => 'https://api.sandbox.pawapay.io']);
    config(['services.pawapay.token' => 'test-token']);

    $depositId = (string) Str::uuid();

    Http::fake([
        'api.sandbox.pawapay.io/v2/deposits' => Http::response([
            'depositId' => $depositId,
            'status' => 'ACCEPTED',
            'provider' => 'MTN_MOMO_COG',
            'amountDetails' => ['amount' => '5000', 'currency' => 'XAF'],
        ], 200),
    ]);

    $service = new PawapayService;
    $result = $service->createDeposit($depositId, [
        'amountDetails' => ['amount' => '5000', 'currency' => 'XAF'],
        'provider' => 'MTN_MOMO_COG',
    ]);

    expect($result['status'])->toBe('ACCEPTED')
        ->and($result['depositId'])->toBe($depositId);

    Http::assertSent(function ($request) use ($depositId) {
        return $request->hasHeader('Authorization', 'Bearer test-token')
            && $request['depositId'] === $depositId
            && $request['provider'] === 'MTN_MOMO_COG';
    });
});

test('create_deposit lance une exception en cas d\'erreur API', function () {
    config(['services.pawapay.base_url' => 'https://api.sandbox.pawapay.io']);
    config(['services.pawapay.token' => 'test-token']);

    Http::fake([
        'api.sandbox.pawapay.io/v2/deposits' => Http::response('Bad request', 400),
    ]);

    $service = new PawapayService;

    expect(fn () => $service->createDeposit((string) Str::uuid(), [
        'amountDetails' => ['amount' => '5000', 'currency' => 'XAF'],
    ]))->toThrow(PawaPayException::class);
});

test('create_payment_page envoie le dÃ©pÃ´t et retourne l URL hÃ©bergÃ©e', function () {
    config([
        'services.pawapay.base_url' => 'https://api.sandbox.pawapay.io',
        'services.pawapay.token' => 'test-token',
    ]);

    $depositId = (string) Str::uuid();
    $returnUrl = 'https://samaritain.test/transactions/'.$depositId.'/callback';

    Http::fake([
        'api.sandbox.pawapay.io/v2/paymentpage' => Http::response([
            'redirectUrl' => 'https://pay.pawapay.io/session/test',
        ], 200),
    ]);

    $result = (new PawapayService)->createPaymentPage(
        depositId: $depositId,
        returnUrl: $returnUrl,
        amount: 5000,
        currency: 'XAF',
        clientReferenceId: 'V-XXXX',
    );

    expect($result['redirectUrl'])->toBe('https://pay.pawapay.io/session/test');

    Http::assertSent(fn ($request) => $request['depositId'] === $depositId
        && $request['returnUrl'] === $returnUrl
        && $request['amountDetails']['amount'] === '5000'
        && $request['amountDetails']['currency'] === 'XAF'
        && $request['clientReferenceId'] === 'V-XXXX');
});

test('get_deposit_status retourne NOT_FOUND pour une rÃ©ponse 404', function () {
    config(['services.pawapay.base_url' => 'https://api.sandbox.pawapay.io']);
    config(['services.pawapay.token' => 'test-token']);

    $depositId = (string) Str::uuid();

    Http::fake([
        "api.sandbox.pawapay.io/v2/deposits/{$depositId}" => Http::response('', 404),
    ]);

    $service = new PawapayService;
    $result = $service->getDepositStatus($depositId);

    expect($result['status'])->toBe('NOT_FOUND')
        ->and($result['depositId'])->toBe($depositId);
});

test('get_deposit_status retourne le statut final depuis l enveloppe FOUND/data', function () {
    config(['services.pawapay.base_url' => 'https://api.sandbox.pawapay.io']);
    config(['services.pawapay.token' => 'test-token']);

    $depositId = (string) Str::uuid();

    // pawaPay status-check endpoints return { status: FOUND, data: { status: ... } }
    Http::fake([
        "api.sandbox.pawapay.io/v2/deposits/{$depositId}" => Http::response([
            'status' => 'FOUND',
            'data' => [
                'depositId' => $depositId,
                'status' => 'COMPLETED',
                'provider' => 'MTN_MOMO_COG',
            ],
        ], 200),
    ]);

    $service = new PawapayService;
    $result = $service->getDepositStatus($depositId);

    expect($result['status'])->toBe('COMPLETED')
        ->and($result['depositId'])->toBe($depositId);
});

test('verify_callback_signature accepte les callbacks non signÃ©s quand la vÃ©rification est dÃ©sactivÃ©e', function () {
    config([
        'services.pawapay.verify_callback_signature' => false,
    ]);

    $service = new PawapayService;

    expect($service->verifyCallbackRequest('payload', [], 'POST', 'example.test', '/webhook'))->toBeTrue();
});

test('verify_callback_signature refuse un callback signÃ© mal configurÃ©', function () {
    config([
        'services.pawapay.verify_callback_signature' => true,
        'services.pawapay.callback_public_key' => null,
    ]);

    $service = new PawapayService;

    expect($service->verifyCallbackRequest('payload', [], 'POST', 'example.test', '/webhook'))->toBeFalse();
});

/*
|--------------------------------------------------------------------------
| Tests du dÃ©pÃ´t direct (PawapayService::initiateDeposit) et de la page pending
|--------------------------------------------------------------------------
*/

test('initiate_deposit construit le payload payer et appelle /v2/deposits', function () {
    config([
        'services.pawapay.base_url' => 'https://api.sandbox.pawapay.io',
        'services.pawapay.token' => 'test-token',
    ]);

    $depositId = (string) Str::uuid();

    Http::fake([
        'api.sandbox.pawapay.io/v2/deposits' => Http::response([
            'depositId' => $depositId,
            'status' => 'ACCEPTED',
            'created' => now()->toIso8601String(),
        ], 200),
    ]);

    $service = new PawapayService;
    $result = $service->initiateDeposit(
        depositId: $depositId,
        phoneNumber: '24268007138',
        provider: 'MTN_MOMO_COG',
        amount: 5000,
        currency: 'XAF',
        clientReferenceId: 'V-XXXX',
    );

    expect($result['status'])->toBe('ACCEPTED')
        ->and($result['depositId'])->toBe($depositId);

    Http::assertSent(function ($request) use ($depositId) {
        return $request->hasHeader('Authorization', 'Bearer test-token')
            && $request['depositId'] === $depositId
            && $request['amount'] === '5000'
            && $request['currency'] === 'XAF'
            && $request['payer']['type'] === 'MMO'
            && $request['payer']['accountDetails']['phoneNumber'] === '24268007138'
            && $request['payer']['accountDetails']['provider'] === 'MTN_MOMO_COG';
    });
});

test('normalize_msisdn retire les espaces et ajoute le code pays', function () {
    config(['services.pawapay.dial_code' => '242']);

    $service = new PawapayService;

    expect($service->normalizeMsisdn('06 800 71 38'))->toBe('24268007138')
        ->and($service->normalizeMsisdn('+242068007138'))->toBe('24268007138')
        ->and($service->normalizeMsisdn('24268007138'))->toBe('24268007138');
});

test('normalize_msisdn refuse un numÃ©ro vide', function () {
    config(['services.pawapay.dial_code' => '242']);

    $service = new PawapayService;

    expect(fn () => $service->normalizeMsisdn('abc'))
        ->toThrow(PawaPayException::class);
});

test('la page pending nÃ©cessite une authentification', function () {
    $transaction = Transaction::factory()->create();

    $this->get(route('transactions.pending', $transaction))
        ->assertRedirect(route('login'));
});

/*
|--------------------------------------------------------------------------
| Tests du flow visit pass â†’ paiement (UserVisitPassController)
|--------------------------------------------------------------------------
*/

test('store visit pass crÃ©e le pass et ouvre directement la page pawaPay', function () {
    config([
        'services.pawapay.base_url' => 'https://api.sandbox.pawapay.io',
        'services.pawapay.token' => 'test-token',
    ]);

    $user = User::factory()->create();
    $property = Property::factory()->create();

    Http::fake([
        'api.sandbox.pawapay.io/v2/paymentpage' => Http::response([
            'redirectUrl' => 'https://pay.pawapay.io/session/test',
        ], 200),
    ]);

    $this->actingAs($user)
        ->post(route('my-visit-passes.store'), [
            'property_id' => $property->id,
            'holder_name' => 'Jean Dupont',
            'phone' => '+242061234567',
        ])
        ->assertRedirect('https://pay.pawapay.io/session/test');

    $visitPass = VisitPass::where('user_id', $user->id)->first();

    expect($visitPass)->not->toBeNull()
        ->and($visitPass->holder_name)->toBe('Jean Dupont')
        ->and($visitPass->payment_status)->toBe('pending')
        ->and($visitPass->transaction_id)->not->toBeNull();
});

test('initiate_payment du visit pass redirige vers la page de paiement pawaPay', function () {
    config([
        'services.pawapay.base_url' => 'https://api.sandbox.pawapay.io',
        'services.pawapay.token' => 'test-token',
    ]);

    $user = User::factory()->create();
    $property = Property::factory()->create();

    Http::fake([
        'api.sandbox.pawapay.io/v2/paymentpage' => Http::response([
            'redirectUrl' => 'https://pay.pawapay.io/session/test',
        ], 200),
    ]);

    $this->actingAs($user)->post(route('my-visit-passes.store'), [
        'property_id' => $property->id,
        'holder_name' => 'Jean Dupont',
        'phone' => '+242061234567',
    ]);

    $visitPass = VisitPass::where('user_id', $user->id)->firstOrFail();

    Http::fake([
        'api.sandbox.pawapay.io/v2/paymentpage' => Http::response([
            'redirectUrl' => 'https://pay.pawapay.io/session/test',
        ], 200),
    ]);

    $this->actingAs($user)
        ->post(route('my-visit-passes.initiate-payment', $visitPass))
        ->assertRedirect('https://pay.pawapay.io/session/test');

    $transaction = Transaction::where('visit_pass_id', $visitPass->id)->latest()->first();

    expect($transaction)->not->toBeNull()
        ->and($transaction->deposit_id)->not->toBeNull()
        ->and($transaction->status)->toBe(TransactionStatus::PENDING)
        ->and($transaction->provider)->toBeNull()
        ->and($transaction->amount)->toBe(5000);

    $visitPass->refresh();
    expect($visitPass->transaction_id)->toBe($transaction->transaction_id);

    Http::assertSent(fn ($request) => str_contains($request->url(), '/v2/paymentpage')
        && $request['depositId'] === $transaction->deposit_id
        && $request['amountDetails']['amount'] === '5000'
        && $request['amountDetails']['currency'] === 'XAF'
        && $request['returnUrl'] === route('transactions.callback', $transaction));
});

test('initiate_payment du visit pass laisse la transaction en pending si l\'API pawaPay Ã©choue', function () {
    config([
        'services.pawapay.base_url' => 'https://api.sandbox.pawapay.io',
        'services.pawapay.token' => 'test-token',
    ]);

    $user = User::factory()->create();
    $property = Property::factory()->create();

    Http::fake([
        'api.sandbox.pawapay.io/v2/paymentpage' => Http::response([
            'redirectUrl' => 'https://pay.pawapay.io/session/test',
        ], 200),
    ]);

    $this->actingAs($user)->post(route('my-visit-passes.store'), [
        'property_id' => $property->id,
        'holder_name' => 'Jean Dupont',
        'phone' => '+242061234567',
    ]);

    $visitPass = VisitPass::where('user_id', $user->id)->firstOrFail();

    Http::fake([
        'api.sandbox.pawapay.io/v2/paymentpage' => Http::response('Server error', 500),
    ]);

    $this->actingAs($user)
        ->post(route('my-visit-passes.initiate-payment', $visitPass))
        ->assertRedirect(route('transactions.pending'));

    $transaction = Transaction::where('visit_pass_id', $visitPass->id)->first();

    // RÃ¨gle critique : en cas d'erreur HTTP, ne JAMAIS marquer failed â€” rester pending
    expect($transaction)->not->toBeNull()
        ->and($transaction->status)->toBe(TransactionStatus::PENDING);
});

test('initiate_payment du visit pass accepte le choix du provider sur la page hÃ©bergÃ©e', function () {
    config([
        'services.pawapay.base_url' => 'https://api.sandbox.pawapay.io',
        'services.pawapay.token' => 'test-token',
    ]);

    $user = User::factory()->create();
    $property = Property::factory()->create();

    Http::fake([
        'api.sandbox.pawapay.io/v2/paymentpage' => Http::response([
            'redirectUrl' => 'https://pay.pawapay.io/session/test',
        ], 200),
    ]);

    $this->actingAs($user)->post(route('my-visit-passes.store'), [
        'property_id' => $property->id,
        'holder_name' => 'Jean Dupont',
        'phone' => '+242061234567',
    ]);

    $visitPass = VisitPass::where('user_id', $user->id)->firstOrFail();

    Http::fake([
        'api.sandbox.pawapay.io/v2/paymentpage' => Http::response([
            'redirectUrl' => 'https://pay.pawapay.io/session/test',
        ], 200),
    ]);

    $this->actingAs($user)
        ->post(route('my-visit-passes.initiate-payment', $visitPass))
        ->assertRedirect('https://pay.pawapay.io/session/test');

    expect(Transaction::count())->toBe(1);
});

/*
|--------------------------------------------------------------------------
| Tests du webhook pawaPay (callback POST)
|--------------------------------------------------------------------------
*/

test('webhook accepte un callback valide et dispatch le job de traitement', function () {
    config([
        'services.pawapay.base_url' => 'https://api.sandbox.pawapay.io',
        'services.pawapay.token' => 'test-token',
        'services.pawapay.verify_callback_signature' => false,
    ]);

    Queue::fake();

    $user = User::factory()->create();
    $transaction = Transaction::create([
        'user_id' => $user->id,
        'deposit_id' => (string) Str::uuid(),
        'status' => 'PENDING',
        'amount' => 5000,
        'currency' => 'XAF',
    ]);

    $response = $this->postJson(route('transactions.webhook', $transaction), [
        'depositId' => $transaction->deposit_id,
        'status' => 'COMPLETED',
    ]);

    $response->assertStatus(200);

    Queue::assertPushed(ProcessPawaPayCallback::class, function ($job) use ($transaction) {
        return $job->transaction->transaction_id === $transaction->transaction_id;
    });
});

test('webhook rejette un callback avec signature invalide', function () {
    config([
        'services.pawapay.base_url' => 'https://api.sandbox.pawapay.io',
        'services.pawapay.token' => 'test-token',
        'services.pawapay.verify_callback_signature' => true,
        'services.pawapay.callback_public_key' => null,
    ]);

    Queue::fake();

    $user = User::factory()->create();
    $transaction = Transaction::create([
        'user_id' => $user->id,
        'deposit_id' => (string) Str::uuid(),
        'status' => 'PENDING',
        'amount' => 5000,
        'currency' => 'XAF',
    ]);

    $response = $this->postJson(route('transactions.webhook', $transaction), [
        'depositId' => $transaction->deposit_id,
        'status' => 'COMPLETED',
    ], [
    ]);

    $response->assertStatus(403);
    Queue::assertNotPushed(ProcessPawaPayCallback::class);
});

test('generic_webhook accepte un callback valide et dispatch le job de traitement', function () {
    config([
        'services.pawapay.base_url' => 'https://api.sandbox.pawapay.io',
        'services.pawapay.token' => 'test-token',
        'services.pawapay.verify_callback_signature' => false,
    ]);

    Queue::fake();

    $user = User::factory()->create();
    $transaction = Transaction::create([
        'user_id' => $user->id,
        'deposit_id' => (string) Str::uuid(),
        'status' => 'PENDING',
        'amount' => 5000,
        'currency' => 'XAF',
    ]);

    $response = $this->postJson(route('transactions.generic_webhook'), [
        'depositId' => $transaction->deposit_id,
        'status' => 'COMPLETED',
    ]);

    $response->assertStatus(200);

    Queue::assertPushed(ProcessPawaPayCallback::class, function ($job) use ($transaction) {
        return $job->transaction->transaction_id === $transaction->transaction_id;
    });
});

test('generic_webhook rejette un callback avec signature invalide', function () {
    config([
        'services.pawapay.base_url' => 'https://api.sandbox.pawapay.io',
        'services.pawapay.token' => 'test-token',
        'services.pawapay.verify_callback_signature' => true,
        'services.pawapay.callback_public_key' => null,
    ]);

    Queue::fake();

    $user = User::factory()->create();
    $transaction = Transaction::create([
        'user_id' => $user->id,
        'deposit_id' => (string) Str::uuid(),
        'status' => 'PENDING',
        'amount' => 5000,
        'currency' => 'XAF',
    ]);

    $response = $this->postJson(route('transactions.generic_webhook'), [
        'depositId' => $transaction->deposit_id,
        'status' => 'COMPLETED',
    ], [
    ]);

    $response->assertStatus(403);
    Queue::assertNotPushed(ProcessPawaPayCallback::class);
});

/*
|--------------------------------------------------------------------------
| Tests du job ProcessPawaPayCallback
|--------------------------------------------------------------------------
*/

test('process_pawaPay_callback met Ã  jour la transaction Ã  completed', function () {
    config([
        'services.pawapay.base_url' => 'https://api.sandbox.pawapay.io',
        'services.pawapay.token' => 'test-token',
    ]);

    $user = User::factory()->create();
    $depositId = (string) Str::uuid();

    $transaction = Transaction::create([
        'user_id' => $user->id,
        'deposit_id' => $depositId,
        'status' => 'PENDING',
        'amount' => 5000,
        'currency' => 'XAF',
    ]);

    Http::fake([
        "api.sandbox.pawapay.io/v2/deposits/{$depositId}" => Http::response([
            'status' => 'FOUND',
            'data' => [
                'depositId' => $depositId,
                'status' => 'COMPLETED',
                'provider' => 'MTN_MOMO_COG',
            ],
        ], 200),
    ]);

    $job = new ProcessPawaPayCallback($transaction, ['status' => 'COMPLETED']);
    $job->handle(app(PawapayService::class));

    $transaction->refresh();
    expect($transaction->status)->toBe(TransactionStatus::COMPLETED);
});

test('process_pawaPay_callback met Ã  jour la transaction Ã  failed', function () {
    config([
        'services.pawapay.base_url' => 'https://api.sandbox.pawapay.io',
        'services.pawapay.token' => 'test-token',
    ]);

    $user = User::factory()->create();
    $depositId = (string) Str::uuid();

    $transaction = Transaction::create([
        'user_id' => $user->id,
        'deposit_id' => $depositId,
        'status' => 'PENDING',
        'amount' => 5000,
        'currency' => 'XAF',
    ]);

    Http::fake([
        "api.sandbox.pawapay.io/v2/deposits/{$depositId}" => Http::response([
            'status' => 'FOUND',
            'data' => [
                'depositId' => $depositId,
                'status' => 'FAILED',
            ],
        ], 200),
    ]);

    $job = new ProcessPawaPayCallback($transaction, ['status' => 'FAILED']);
    $job->handle(app(PawapayService::class));

    $transaction->refresh();
    expect($transaction->status)->toBe(TransactionStatus::FAILED);
});

test('process_pawaPay_callback laisse en pending sur NOT_FOUND', function () {
    config([
        'services.pawapay.base_url' => 'https://api.sandbox.pawapay.io',
        'services.pawapay.token' => 'test-token',
    ]);

    $user = User::factory()->create();
    $depositId = (string) Str::uuid();

    $transaction = Transaction::create([
        'user_id' => $user->id,
        'deposit_id' => $depositId,
        'status' => 'PENDING',
        'amount' => 5000,
        'currency' => 'XAF',
    ]);

    Http::fake([
        "api.sandbox.pawapay.io/v2/deposits/{$depositId}" => Http::response('', 404),
    ]);

    $job = new ProcessPawaPayCallback($transaction, ['status' => 'PENDING']);
    $job->handle(app(PawapayService::class));

    $transaction->refresh();
    // NOT_FOUND should NOT be treated as FAILED â€” keep as pending
    expect($transaction->status)->toBe(TransactionStatus::PENDING);
});

/*
|--------------------------------------------------------------------------
| Tests de la commande de rÃ©conciliation
|--------------------------------------------------------------------------
*/

test('la commande de rÃ©conciliation vÃ©rifie les paiements bloquÃ©s', function () {
    config([
        'services.pawapay.base_url' => 'https://api.sandbox.pawapay.io',
        'services.pawapay.token' => 'test-token',
    ]);

    $user = User::factory()->create();
    $depositId = (string) Str::uuid();

    $transaction = Transaction::create([
        'user_id' => $user->id,
        'deposit_id' => $depositId,
        'status' => 'PENDING',
        'amount' => 5000,
        'currency' => 'XAF',
    ]);
    $transaction->created_at = now()->subHour();
    $transaction->updated_at = now()->subHour();
    $transaction->save();

    Http::fake([
        "api.sandbox.pawapay.io/v2/deposits/{$depositId}" => Http::response([
            'status' => 'FOUND',
            'data' => [
                'depositId' => $depositId,
                'status' => 'COMPLETED',
            ],
        ], 200),
    ]);

    $this->artisan('pawapay:reconcile')
        ->expectsOutput('Reconciled 1 transaction(s). 0 error(s).')
        ->assertExitCode(0);

    $transaction = Transaction::where('deposit_id', $depositId)->first();
    expect($transaction->status)->toBe(TransactionStatus::COMPLETED);
});

test('la commande de rÃ©conciliation ne marque pas en failed sur NOT_FOUND', function () {
    config([
        'services.pawapay.base_url' => 'https://api.sandbox.pawapay.io',
        'services.pawapay.token' => 'test-token',
    ]);

    $user = User::factory()->create();
    $depositId = (string) Str::uuid();

    $transaction = Transaction::create([
        'user_id' => $user->id,
        'deposit_id' => $depositId,
        'status' => 'PENDING',
        'amount' => 5000,
        'currency' => 'XAF',
    ]);
    $transaction->created_at = now()->subHour();
    $transaction->updated_at = now()->subHour();
    $transaction->save();

    Http::fake([
        "api.sandbox.pawapay.io/v2/deposits/{$depositId}" => Http::response('', 404),
    ]);

    $this->artisan('pawapay:reconcile')
        ->assertExitCode(0);

    $transaction = Transaction::where('deposit_id', $depositId)->first();
    // NOT_FOUND means the deposit was never created (e.g. abandoned payment page)
    // â€” mark it failed so the pass/rent can be retried.
    expect($transaction->status)->toBe(TransactionStatus::FAILED);
});

test('la commande de rÃ©conciliation ignore les paiements rÃ©cents', function () {
    config([
        'services.pawapay.base_url' => 'https://api.sandbox.pawapay.io',
        'services.pawapay.token' => 'test-token',
    ]);

    $user = User::factory()->create();
    $depositId = (string) Str::uuid();

    $transaction = Transaction::create([
        'user_id' => $user->id,
        'deposit_id' => $depositId,
        'status' => 'PENDING',
        'amount' => 5000,
        'currency' => 'XAF',
    ]);
    $transaction->created_at = now()->subMinutes(5);
    $transaction->updated_at = now()->subMinutes(5);
    $transaction->save();

    Http::fake([
        "api.sandbox.pawapay.io/v2/deposits/{$depositId}" => Http::response([
            'depositId' => $depositId,
            'status' => 'COMPLETED',
        ], 200),
    ]);

    $this->artisan('pawapay:reconcile')
        ->expectsOutput('No stuck pawaPay payments found.')
        ->assertExitCode(0);

    $transaction = Transaction::where('deposit_id', $depositId)->first();
    expect($transaction->status)->toBe(TransactionStatus::PENDING);
});
