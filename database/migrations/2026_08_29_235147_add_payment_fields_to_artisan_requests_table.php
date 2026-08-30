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
        Schema::table('artisan_requests', function (Blueprint $table) {
            $table->bigInteger('total_amount')->nullable()->after('statut');
            $table->bigInteger('down_payment_amount')->nullable()->after('total_amount');
            $table->string('payment_status')->default('UNPAID')->after('down_payment_amount'); // UNPAID, DOWN_PAYMENT_PAID, FULLY_PAID
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('artisan_requests', function (Blueprint $table) {
            $table->dropColumn(['total_amount', 'down_payment_amount', 'payment_status']);
        });
    }
};
