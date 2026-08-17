<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('factures', function (Blueprint $table) {
            $table->id();
            $table->foreignId('chantier_id')->constrained('artisan_chantiers')->cascadeOnDelete();
            $table->string('numero')->unique();
            $table->decimal('montant_ht', 12, 2);
            $table->decimal('tva', 12, 2)->nullable();
            $table->decimal('montant_ttc', 12, 2);
            $table->date('date_emission');
            $table->date('date_echeance')->nullable();
            $table->string('statut')->default('brouillon'); // brouillon, envoyee, payee, annulee
            $table->text('description')->nullable();
            $table->timestamps();

            $table->index(['chantier_id', 'statut', 'date_emission']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('factures');
    }
};
