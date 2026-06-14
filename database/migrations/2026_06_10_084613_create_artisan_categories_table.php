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
        Schema::create('artisan_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->timestamps();
        });

        Schema::create('artisan_artisan_category', function (Blueprint $table) {
            $table->foreignId('artisan_id')->constrained()->cascadeOnDelete();
            $table->foreignId('artisan_category_id')->constrained()->cascadeOnDelete();
            $table->primary(['artisan_id', 'artisan_category_id']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('artisan_artisan_category');
        Schema::dropIfExists('artisan_categories');
    }
};
