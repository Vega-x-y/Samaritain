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
        Schema::table('hotels', function (Blueprint $table) {
            // Supprimer les colonnes existantes
            $table->dropForeign(['category_id']);
            $table->dropColumn([
                'latitude',
                'longitude',
                'price_type',
                'category_id',
            ]);

            // Ajouter la nouvelle colonne price_per_hour
            $table->integer('price_per_hour')->after('price_per_night');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('hotels', function (Blueprint $table) {
            // Supprimer price_per_hour
            $table->dropColumn('price_per_hour');

            // Restaurer les colonnes supprimées
            $table->decimal('latitude', 10, 7)->nullable()->after('address');
            $table->decimal('longitude', 10, 7)->nullable()->after('latitude');
            $table->string('price_type')->default('per_night')->after('price_per_night');
            $table->foreignId('category_id')->nullable()->constrained('categories')->nullOnDelete()->after('longitude');
        });
    }
};
