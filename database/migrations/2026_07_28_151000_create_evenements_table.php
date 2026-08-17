<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('evenements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('artisan_id')->constrained()->cascadeOnDelete();
            $table->foreignId('chantier_id')->nullable()->constrained('artisan_chantiers')->nullOnDelete();
            $table->string('titre');
            $table->dateTime('date_debut');
            $table->dateTime('date_fin');
            $table->string('type', 20); // intervention, reunion, deplacement
            $table->text('description')->nullable();
            $table->timestamps();

            $table->index(['artisan_id', 'date_debut']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('evenements');
    }
};
