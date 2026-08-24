<?php

use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use App\Livewire\Payment\TransactionStatus as TransactionStatusComponent;
use App\Models\Transaction;
use App\Models\User;
use Livewire\Livewire;

test('transaction status component renders', function () {
    $user = User::factory()->create();
    $transaction = Transaction::factory()->create([
        'user_id' => $user->id,
        'status' => TransactionStatus::COMPLETED,
    ]);

    Livewire::actingAs($user)
        ->test(TransactionStatusComponent::class, ['transactionId' => $transaction->transaction_id])
        ->assertStatus(200)
        ->assertSee($transaction->transaction_id);
});

test('transaction status shows all details', function () {
    $user = User::factory()->create();
    $transaction = Transaction::factory()->create([
        'user_id' => $user->id,
        'type' => TransactionType::DEPOSIT,
        'status' => TransactionStatus::COMPLETED,
        'amount' => 15000,
        'currency' => 'XAF',
        'provider' => 'MTN_MOMO_COG',
    ]);

    Livewire::actingAs($user)
        ->test(TransactionStatusComponent::class, ['transactionId' => $transaction->transaction_id])
        ->assertSee($transaction->type->label())
        ->assertSee('150') // Amount divided by 100
        ->assertSee($transaction->currency)
        ->assertSee($transaction->status->label());
});

test('transaction status prevents unauthorized access', function () {
    $owner = User::factory()->create();
    $otherUser = User::factory()->create();

    $transaction = Transaction::factory()->create([
        'user_id' => $owner->id,
    ]);

    Livewire::actingAs($otherUser)
        ->test(TransactionStatusComponent::class, ['transactionId' => $transaction->transaction_id])
        ->assertForbidden();
});

test('transaction status shows retry button for failed transactions', function () {
    $user = User::factory()->create();
    $transaction = Transaction::factory()->create([
        'user_id' => $user->id,
        'status' => TransactionStatus::FAILED,
    ]);

    Livewire::actingAs($user)
        ->test(TransactionStatusComponent::class, ['transactionId' => $transaction->transaction_id])
        ->assertSee('Réessayer');
});

test('transaction status hides retry button for successful transactions', function () {
    $user = User::factory()->create();
    $transaction = Transaction::factory()->create([
        'user_id' => $user->id,
        'status' => TransactionStatus::COMPLETED,
    ]);

    Livewire::actingAs($user)
        ->test(TransactionStatusComponent::class, ['transactionId' => $transaction->transaction_id])
        ->assertDontSee('Réessayer');
});

test('transaction status enables polling for pending transactions', function () {
    $user = User::factory()->create();
    $transaction = Transaction::factory()->create([
        'user_id' => $user->id,
        'status' => TransactionStatus::PENDING,
    ]);

    Livewire::actingAs($user)
        ->test(TransactionStatusComponent::class, ['transactionId' => $transaction->transaction_id])
        ->assertSet('shouldPoll', true);
});

test('transaction status disables polling for completed transactions', function () {
    $user = User::factory()->create();
    $transaction = Transaction::factory()->create([
        'user_id' => $user->id,
        'status' => TransactionStatus::COMPLETED,
    ]);

    Livewire::actingAs($user)
        ->test(TransactionStatusComponent::class, ['transactionId' => $transaction->transaction_id])
        ->assertSet('shouldPoll', false);
});

test('transaction status shows failure reason when available', function () {
    $user = User::factory()->create();
    $transaction = Transaction::factory()->create([
        'user_id' => $user->id,
        'status' => TransactionStatus::FAILED,
        'raw_response' => [
            'failureReason' => [
                'failureCode' => 'INSUFFICIENT_BALANCE',
                'failureMessage' => 'Le solde est insuffisant',
            ],
        ],
    ]);

    Livewire::actingAs($user)
        ->test(TransactionStatusComponent::class, ['transactionId' => $transaction->transaction_id])
        ->assertSee('INSUFFICIENT_BALANCE');
});
