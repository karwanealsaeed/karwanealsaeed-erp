<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customer_documents', function (Blueprint $table) {
            $table->json('document_types')->nullable()->after('document_type');
            $table->json('documents')->nullable()->after('file_size');
        });
    }

    public function down(): void
    {
        Schema::table('customer_documents', function (Blueprint $table) {
            $table->dropColumn([
                'document_types',
                'documents',
            ]);
        });
    }
};