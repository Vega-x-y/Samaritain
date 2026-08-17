<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            if (! Schema::hasColumn('messages', 'document_id')) {
                $table->foreignId('document_id')->nullable()->after('fichier_taille');
                $table->foreign('document_id')->references('id')->on('documents')->onDelete('set null');
            }
        });
    }

    public function down(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            if (Schema::hasColumn('messages', 'document_id')) {
                $table->dropForeign(['document_id']);
                $table->dropColumn('document_id');
            }
        });
    }
};
