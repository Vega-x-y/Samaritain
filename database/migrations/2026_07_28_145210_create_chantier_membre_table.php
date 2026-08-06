<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chantier_membre', function (Blueprint $table) {
            $table->id();
            $table->foreignId('chantier_id')->constrained('artisan_chantiers')->cascadeOnDelete();
            $table->foreignId('membre_equipe_id')->constrained('membre_equipe')->cascadeOnDelete();
            $table->string('role_sur_chantier', 100)->nullable();
            $table->timestamps();

            $table->unique(['chantier_id', 'membre_equipe_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chantier_membre');
    }
};
