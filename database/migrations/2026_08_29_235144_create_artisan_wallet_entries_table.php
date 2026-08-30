<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('artisan_wallet_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('artisan_wallet_id')->constrained()->cascadeOnDelete();
            $table->uuid('transaction_id')->nullable();
            $table->string('kind'); // deposit, payout, fee
            $table->bigInteger('amount');
            $table->json('metadata')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('artisan_wallet_entries');
    }
};
