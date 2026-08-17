<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hotels', function (Blueprint $table) {
            $table->id();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->string('title');
            $table->longText('description')->nullable();
            $table->integer('price_per_night');
            $table->string('price_type')->default('per_night');
            $table->integer('star_rating')->default(3);
            $table->integer('rooms')->default(1);
            $table->integer('bathrooms')->default(1);
            $table->boolean('furnished')->default(false);
            $table->string('address');
            $table->string('slug')->unique();
            $table->string('status')->default('available');
            $table->boolean('is_verify')->default(false);
            $table->boolean('is_active')->default(true);
            $table->integer('views')->default(0);
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->foreignId('category_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->foreignId('city_id')->nullable()->constrained('cities')->nullOnDelete();
            $table->foreignId('arrondissement_id')->nullable()->constrained('arrondissements')->nullOnDelete();
            $table->timestamp('conditions_accepted_at')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index('slug');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hotels');
    }
};
