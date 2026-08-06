<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chantier_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('chantier_id')->constrained('artisan_chantiers')->cascadeOnDelete();
            $table->string('type'); // acompte, solde, remboursement
            $table->decimal('montant', 12, 2);
            $table->date('date');
            $table->string('statut')->default('en_attente'); // en_attente, recu, rembourse
            $table->string('reference')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['chantier_id', 'type', 'statut']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chantier_transactions');
    }
};
