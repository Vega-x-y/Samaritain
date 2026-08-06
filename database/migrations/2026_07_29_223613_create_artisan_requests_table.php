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
        Schema::create('artisan_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('artisan_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('type')->default('information'); // information, devis, rendez-vous
            $table->text('message');
            $table->string('statut')->default('en_attente'); // en_attente, acceptee, refusee
            $table->text('reponse')->nullable();
            $table->timestamp('date_reponse')->nullable();
            $table->timestamps();

            $table->index(['artisan_id', 'statut']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('artisan_requests');
    }
};
