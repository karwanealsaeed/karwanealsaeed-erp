<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table(
            'customer_payment_uploads',
            function (Blueprint $table): void {
                $table->string('receipt_type', 30)
                    ->default('Invoice Payment')
                    ->after('service_type')
                    ->index();

                $table->foreignId('invoice_id')
                    ->nullable()
                    ->after('receipt_type')
                    ->constrained('invoices')
                    ->nullOnDelete();

                $table->foreignId(
                    'receipt_account_head_id'
                )
                    ->nullable()
                    ->after('invoice_id')
                    ->constrained('account_heads')
                    ->nullOnDelete();

                $table->foreignId(
                    'receivable_account_head_id'
                )
                    ->nullable()
                    ->after('receipt_account_head_id')
                    ->constrained('account_heads')
                    ->nullOnDelete();

                $table->foreignId(
                    'advance_account_head_id'
                )
                    ->nullable()
                    ->after('receivable_account_head_id')
                    ->constrained('account_heads')
                    ->nullOnDelete();

                /*
                 * Legacy approved receipt ke liye ye field
                 * batayegi ke invoice balance already update
                 * hua tha ya nahi.
                 */
                $table->boolean(
                    'invoice_balance_applied'
                )
                    ->default(false)
                    ->after('account_voucher_id');

                $table->foreignId('posted_by')
                    ->nullable()
                    ->after('invoice_balance_applied')
                    ->constrained('users')
                    ->nullOnDelete();

                $table->timestamp('posted_at')
                    ->nullable()
                    ->after('posted_by');

                $table->foreignId('rejected_by')
                    ->nullable()
                    ->after('posted_at')
                    ->constrained('users')
                    ->nullOnDelete();

                $table->timestamp('rejected_at')
                    ->nullable()
                    ->after('rejected_by');

                $table->text('rejection_reason')
                    ->nullable()
                    ->after('rejected_at');

                $table->foreignId('voided_by')
                    ->nullable()
                    ->after('rejection_reason')
                    ->constrained('users')
                    ->nullOnDelete();

                $table->timestamp('voided_at')
                    ->nullable()
                    ->after('voided_by');

                $table->text('void_reason')
                    ->nullable()
                    ->after('voided_at');
            }
        );
    }

    public function down(): void
    {
        Schema::table(
            'customer_payment_uploads',
            function (Blueprint $table): void {
                $table->dropConstrainedForeignId(
                    'invoice_id'
                );

                $table->dropConstrainedForeignId(
                    'receipt_account_head_id'
                );

                $table->dropConstrainedForeignId(
                    'receivable_account_head_id'
                );

                $table->dropConstrainedForeignId(
                    'advance_account_head_id'
                );

                $table->dropConstrainedForeignId(
                    'posted_by'
                );

                $table->dropConstrainedForeignId(
                    'rejected_by'
                );

                $table->dropConstrainedForeignId(
                    'voided_by'
                );

                $table->dropColumn([
                    'receipt_type',
                    'invoice_balance_applied',
                    'posted_at',
                    'rejected_at',
                    'rejection_reason',
                    'voided_at',
                    'void_reason',
                ]);
            }
        );
    }
};