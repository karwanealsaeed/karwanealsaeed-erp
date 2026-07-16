<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('account_vouchers', function (Blueprint $table): void {
            if (! Schema::hasColumn('account_vouchers', 'source_model')) {
                $table->string('source_model')->nullable()->after('created_by');
            }

            if (! Schema::hasColumn('account_vouchers', 'source_id')) {
                $table->unsignedBigInteger('source_id')->nullable()->after('source_model');
            }

            if (! Schema::hasColumn('account_vouchers', 'source_type')) {
                $table->string('source_type')->nullable()->after('source_id');
            }

            if (! Schema::hasColumn('account_vouchers', 'source_no')) {
                $table->string('source_no')->nullable()->after('source_type');
            }

            $table->index(['source_model', 'source_id']);
        });
    }

    public function down(): void
    {
        Schema::table('account_vouchers', function (Blueprint $table): void {
            $table->dropIndex(['source_model', 'source_id']);

            $table->dropColumn([
                'source_model',
                'source_id',
                'source_type',
                'source_no',
            ]);
        });
    }
};