<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('depenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('chantier_id')->constrained('artisan_chantiers')->cascadeOnDelete();
            $table->string('categorie'); // materiaux, main_oeuvre, transport, autre
            $table->decimal('montant', 12, 2);
            $table->date('date');
            $table->string('justificatif')->nullable(); // path vers le fichier
            $table->text('description')->nullable();
            $table->string('fournisseur')->nullable();
            $table->timestamps();

            $table->index(['chantier_id', 'categorie', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('depenses');
    }
};
