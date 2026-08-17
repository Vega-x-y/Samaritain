<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mouvements_stock', function (Blueprint $table) {
            $table->id();
            $table->foreignId('article_id')->constrained('articles_stock')->cascadeOnDelete();
            $table->string('type', 20); // entree ou sortie
            $table->integer('quantite');
            $table->text('motif')->nullable();
            $table->timestamp('date');
            $table->timestamps();

            $table->index(['article_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mouvements_stock');
    }
};
