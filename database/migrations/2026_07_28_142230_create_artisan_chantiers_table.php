<?php

use App\Enums\ChantierStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('artisan_chantiers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('artisan_id')->constrained()->cascadeOnDelete();
            $table->foreignId('client_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('nom');
            $table->string('type', 50); // plomberie, electricite, peinture, maconnerie, menuiserie
            $table->string('statut', 20)->default(ChantierStatus::DEVIS->value);
            $table->decimal('budget', 10, 2)->nullable();
            $table->date('date_debut')->nullable();
            $table->date('date_fin')->nullable();
            $table->string('priorite', 20)->nullable(); // haute, moyenne, basse
            $table->text('materiel')->nullable();
            $table->text('note_client')->nullable();
            $table->json('checklist')->nullable();
            $table->json('messages')->nullable();
            $table->json('photos')->nullable();
            $table->json('devis_lines')->nullable();
            $table->boolean('acompte_paye')->default(false);
            $table->boolean('solde_paye')->default(false);
            $table->boolean('reception_validee')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('artisan_chantiers');
    }
};
