<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('conversations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('artisan_id')->constrained()->cascadeOnDelete();
            $table->foreignId('client_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('membre_equipe_id')->nullable()->constrained('membre_equipe')->nullOnDelete();
            $table->string('sujet')->nullable();
            $table->boolean('lu')->default(false);
            $table->timestamp('dernier_message_at')->nullable();
            $table->timestamps();

            $table->index(['artisan_id', 'dernier_message_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('conversations');
    }
};
