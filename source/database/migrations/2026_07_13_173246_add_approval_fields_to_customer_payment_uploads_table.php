<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customer_payment_uploads', function (Blueprint $table): void {
            if (! Schema::hasColumn('customer_payment_uploads', 'approved_at')) {
                $table->timestamp('approved_at')->nullable()->after('status');
            }

            if (! Schema::hasColumn('customer_payment_uploads', 'approved_by')) {
                $table->foreignId('approved_by')
                    ->nullable()
                    ->after('approved_at')
                    ->constrained('users')
                    ->nullOnDelete();
            }

            if (! Schema::hasColumn('customer_payment_uploads', 'account_voucher_id')) {
                $table->unsignedBigInteger('account_voucher_id')->nullable()->after('approved_by');
            }
        });
    }

    public function down(): void
    {
        Schema::table('customer_payment_uploads', function (Blueprint $table): void {
            if (Schema::hasColumn('customer_payment_uploads', 'approved_by')) {
                $table->dropConstrainedForeignId('approved_by');
            }

            if (Schema::hasColumn('customer_payment_uploads', 'approved_at')) {
                $table->dropColumn('approved_at');
            }

            if (Schema::hasColumn('customer_payment_uploads', 'account_voucher_id')) {
                $table->dropColumn('account_voucher_id');
            }
        });
    }
};  