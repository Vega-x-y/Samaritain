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
        Schema::create('wallet_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('owner_wallet_id')->constrained()->cascadeOnDelete();
            $table->uuid('transaction_id');
            $table->string('kind');
            $table->unsignedBigInteger('amount');
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->unique(['transaction_id', 'kind']);
            $table->index(['owner_wallet_id', 'kind']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wallet_entries');
    }
};
