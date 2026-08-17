<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->string('fichier_path')->nullable();
            $table->string('fichier_nom')->nullable();
            $table->string('fichier_mime')->nullable();
            $table->unsignedInteger('fichier_taille')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->dropColumn(['fichier_path', 'fichier_nom', 'fichier_mime', 'fichier_taille']);
        });
    }
};
