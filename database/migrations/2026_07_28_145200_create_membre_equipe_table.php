<?php

use App\Enums\MembreStatut;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('membre_equipe', function (Blueprint $table) {
            $table->id();
            $table->foreignId('artisan_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('nom');
            $table->string('role', 100);
            $table->string('telephone', 20);
            $table->string('email')->nullable();
            $table->string('statut', 20)->default(MembreStatut::ACTIF->value);
            $table->timestamps();

            $table->index(['artisan_id', 'nom']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('membre_equipe');
    }
};
