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
        Schema::create('boutique_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('boutique_id')->constrained()->cascadeOnDelete();
            $table->string('image_url');
            $table->boolean('cover_image')->default(false);
            $table->timestamps();
        });

        Schema::create('bureau_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bureau_id')->constrained()->cascadeOnDelete();
            $table->string('image_url');
            $table->boolean('cover_image')->default(false);
            $table->timestamps();
        });

        Schema::create('amenity_boutique', function (Blueprint $table) {
            $table->foreignId('amenity_id')->constrained()->cascadeOnDelete();
            $table->foreignId('boutique_id')->constrained()->cascadeOnDelete();
            $table->primary(['amenity_id', 'boutique_id']);
        });

        Schema::create('amenity_bureau', function (Blueprint $table) {
            $table->foreignId('amenity_id')->constrained()->cascadeOnDelete();
            $table->foreignId('bureau_id')->constrained()->cascadeOnDelete();
            $table->primary(['amenity_id', 'bureau_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('amenity_bureau');
        Schema::dropIfExists('amenity_boutique');
        Schema::dropIfExists('bureau_images');
        Schema::dropIfExists('boutique_images');
    }
};

