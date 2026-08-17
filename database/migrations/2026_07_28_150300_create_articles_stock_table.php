<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('articles_stock', function (Blueprint $table) {
            $table->id();
            $table->foreignId('artisan_id')->constrained()->cascadeOnDelete();
            $table->string('nom');
            $table->string('reference')->nullable();
            $table->string('categorie')->nullable();
            $table->integer('quantite')->default(0);
            $table->integer('seuil_alerte')->default(5);
            $table->decimal('prix_unitaire', 10, 2)->nullable();
            $table->string('fournisseur')->nullable();
            $table->timestamps();

            $table->index(['artisan_id', 'nom']);
            $table->index(['artisan_id', 'reference']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('articles_stock');
    }
};
