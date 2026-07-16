<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('supplier_bills', function (Blueprint $table): void {
            $table->foreignId('expense_account_head_id')
                ->nullable()
                ->after('service_type')
                ->constrained('account_heads')
                ->nullOnDelete();

            $table->foreignId('payable_account_head_id')
                ->nullable()
                ->after('expense_account_head_id')
                ->constrained('account_heads')
                ->nullOnDelete();

            $table->string('approval_status', 30)
                ->default('Pending')
                ->after('status')
                ->index();

            $table->foreignId('approved_by')
                ->nullable()
                ->after('approval_status')
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('approved_at')
                ->nullable()
                ->after('approved_by');

            $table->foreignId('rejected_by')
                ->nullable()
                ->after('approved_at')
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('rejected_at')
                ->nullable()
                ->after('rejected_by');

            $table->text('rejection_reason')
                ->nullable()
                ->after('rejected_at');

            $table->foreignId('posted_by')
                ->nullable()
                ->after('account_voucher_id')
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('posted_at')
                ->nullable()
                ->after('posted_by');
        });

        Schema::table('supplier_payments', function (Blueprint $table): void {
            $table->foreignId('payment_account_head_id')
                ->nullable()
                ->after('payment_method')
                ->constrained('account_heads')
                ->nullOnDelete();

            $table->foreignId('posted_by')
                ->nullable()
                ->after('account_voucher_id')
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('posted_at')
                ->nullable()
                ->after('posted_by');
        });
    }

    public function down(): void
    {
        Schema::table('supplier_payments', function (Blueprint $table): void {
            $table->dropConstrainedForeignId(
                'payment_account_head_id'
            );

            $table->dropConstrainedForeignId('posted_by');

            $table->dropColumn('posted_at');
        });

        Schema::table('supplier_bills', function (Blueprint $table): void {
            $table->dropConstrainedForeignId(
                'expense_account_head_id'
            );

            $table->dropConstrainedForeignId(
                'payable_account_head_id'
            );

            $table->dropConstrainedForeignId('approved_by');
            $table->dropConstrainedForeignId('rejected_by');
            $table->dropConstrainedForeignId('posted_by');

            $table->dropColumn([
                'approval_status',
                'approved_at',
                'rejected_at',
                'rejection_reason',
                'posted_at',
            ]);
        });
    }
};