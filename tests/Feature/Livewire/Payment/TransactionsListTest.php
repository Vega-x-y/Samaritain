<?php

use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use App\Livewire\Payment\TransactionsList;
use App\Models\Transaction;
use App\Models\User;
use Livewire\Livewire;

test('transactions list component renders', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(TransactionsList::class)
        ->assertStatus(200);
});

test('transactions list shows user transactions', function () {
    $user = User::factory()->create();

    // Create transactions for this user
    $deposit = Transaction::factory()->create([
        'user_id' => $user->id,
        'type' => TransactionType::DEPOSIT,
        'status' => TransactionStatus::COMPLETED,
        'amount' => 10000,
    ]);

    $payout = Transaction::factory()->create([
        'user_id' => $user->id,
        'type' => TransactionType::PAYOUT,
        'status' => TransactionStatus::PENDING,
        'amount' => 5000,
    ]);

    // Create transaction for another user (should not be shown)
    $otherUser = User::factory()->create();
    Transaction::factory()->create([
        'user_id' => $otherUser->id,
        'type' => TransactionType::DEPOSIT,
    ]);

    Livewire::actingAs($user)
        ->test(TransactionsList::class)
        ->assertSee('100') // Deposit amount divided by 100
        ->assertSee('50') // Payout amount divided by 100
        ->assertSee($deposit->status->label())
        ->assertSee($payout->status->label());
});

test('transactions list filters by type', function () {
    $user = User::factory()->create();

    $deposit = Transaction::factory()->create([
        'user_id' => $user->id,
        'type' => TransactionType::DEPOSIT,
        'amount' => 10000,
    ]);

    $payout = Transaction::factory()->create([
        'user_id' => $user->id,
        'type' => TransactionType::PAYOUT,
        'amount' => 5000,
    ]);

    // Test deposits filter
    Livewire::actingAs($user)
        ->test(TransactionsList::class)
        ->set('filterType', 'deposits')
        ->assertSee('100')
        ->assertDontSee('50');

    // Test payouts filter
    Livewire::actingAs($user)
        ->test(TransactionsList::class)
        ->set('filterType', 'payouts')
        ->assertSee('50')
        ->assertDontSee('100');
});

test('transactions list enables polling when pending transactions exist', function () {
    $user = User::factory()->create();

    Transaction::factory()->create([
        'user_id' => $user->id,
        'status' => TransactionStatus::PENDING,
    ]);

    Livewire::actingAs($user)
        ->test(TransactionsList::class)
        ->assertSet('hasPendingTransactions', true);
});

test('transactions list paginates results', function () {
    $user = User::factory()->create();

    // Create 15 transactions (more than 10 per page)
    Transaction::factory()->count(15)->create([
        'user_id' => $user->id,
    ]);

    $component = Livewire::actingAs($user)
        ->test(TransactionsList::class);

    expect($component->get('transactions')->count())->toBe(10);
    expect($component->get('transactions')->total())->toBe(15);
});
