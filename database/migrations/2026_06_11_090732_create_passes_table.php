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
        Schema::create('passes', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('holder_name');
            $table->string('phone');
            $table->string('email')->nullable();
            $table->integer('allowed_visits');
            $table->integer('remaining_visits');
            $table->timestamp('start_date');
            $table->timestamp('expiration_date');
            $table->string('qr_code_path')->nullable();
            $table->enum('status', ['actif', 'expiré', 'utilisé', 'suspendu'])->default('actif');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'expiration_date']);
            $table->index('uuid');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('passes');
    }
};
