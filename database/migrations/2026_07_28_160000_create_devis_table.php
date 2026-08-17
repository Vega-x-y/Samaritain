<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('devis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('chantier_id')->constrained('artisan_chantiers')->cascadeOnDelete();
            $table->string('numero')->unique();
            $table->string('statut')->default('brouillon'); // brouillon, envoye, signe
            $table->date('date_envoi')->nullable();
            $table->date('date_signature')->nullable();
            $table->decimal('montant_ht', 12, 2)->nullable();
            $table->decimal('tva', 12, 2)->nullable();
            $table->decimal('montant_ttc', 12, 2)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['chantier_id', 'statut']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('devis');
    }
};
