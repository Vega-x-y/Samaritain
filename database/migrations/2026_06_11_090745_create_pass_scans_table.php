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
        Schema::create('pass_scans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pass_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->timestamp('scanned_at');
            $table->string('ip_address', 45);
            $table->string('user_agent');
            $table->string('device_info')->nullable();
            $table->timestamps();

            $table->index(['pass_id', 'scanned_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pass_scans');
    }
};
