<?php

use App\Enums\ClientType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('artisan_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('nom');
            $table->string('telephone', 20);
            $table->string('email')->nullable();
            $table->text('adresse')->nullable();
            $table->string('type', 20)->default(ClientType::PARTICULIER->value);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['artisan_id', 'nom']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};
