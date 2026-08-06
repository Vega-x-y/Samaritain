<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            if (! Schema::hasColumn('documents', 'status')) {
                $table->string('status')->default('draft')->after('metadata');
            }
            if (! Schema::hasColumn('documents', 'signed_at')) {
                $table->timestamp('signed_at')->nullable()->after('status');
            }
            if (! Schema::hasColumn('documents', 'signature_data')) {
                $table->json('signature_data')->nullable()->after('signed_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            if (Schema::hasColumn('documents', 'signature_data')) {
                $table->dropColumn('signature_data');
            }
            if (Schema::hasColumn('documents', 'signed_at')) {
                $table->dropColumn('signed_at');
            }
            if (Schema::hasColumn('documents', 'status')) {
                $table->dropColumn('status');
            }
        });
    }
};
