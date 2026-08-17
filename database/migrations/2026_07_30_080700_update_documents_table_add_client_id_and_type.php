<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            if (! Schema::hasColumn('documents', 'client_id')) {
                $table->foreignId('client_id')->nullable()->after('chantier_id')->constrained('clients')->nullOnDelete();
            }
            if (! Schema::hasColumn('documents', 'date_modification')) {
                $table->timestamp('date_modification')->nullable()->after('size');
            }
        });

        // Ajouter l'index s'il n'existe pas déjà
        try {
            Schema::table('documents', function (Blueprint $table) {
                $table->index(['client_id', 'type']);
            });
        } catch (Exception $e) {
            // Index déjà existant, on ignore
        }
    }

    public function down(): void
    {
        try {
            Schema::table('documents', function (Blueprint $table) {
                $table->dropIndex(['client_id', 'type']);
            });
        } catch (Exception $e) {
            // L'index n'existe pas
        }

        Schema::table('documents', function (Blueprint $table) {
            if (Schema::hasColumn('documents', 'date_modification')) {
                $table->dropColumn('date_modification');
            }
        });

        if (Schema::hasColumn('documents', 'client_id')) {
            Schema::table('documents', function (Blueprint $table) {
                $table->dropForeign(['client_id']);
                $table->dropColumn('client_id');
            });
        }
    }
};
