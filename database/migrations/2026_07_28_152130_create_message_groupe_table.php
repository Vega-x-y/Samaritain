<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('message_groupe', function (Blueprint $table) {
            $table->id();
            $table->foreignId('groupe_id')->constrained()->cascadeOnDelete();
            $table->string('expediteur_type'); // artisan, client, equipe
            $table->unsignedBigInteger('expediteur_id');
            $table->text('contenu');
            $table->boolean('lu')->default(false);
            $table->timestamps();

            $table->index(['groupe_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('message_groupe');
    }
};
