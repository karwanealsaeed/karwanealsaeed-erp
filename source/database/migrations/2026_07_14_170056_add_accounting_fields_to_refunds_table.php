<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('refunds', 'refund_type')) {
            Schema::table('refunds', function (Blueprint $table): void {
                $table->string('refund_type', 40)
                    ->default('Invoice Refund')
                    ->index();
            });
        }

        if (! Schema::hasColumn('refunds', 'invoice_id')) {
            Schema::table('refunds', function (Blueprint $table): void {
                $table->foreignId('invoice_id')
                    ->nullable()
                    ->constrained('invoices')
                    ->nullOnDelete();
            });
        }

        if (! Schema::hasColumn('refunds', 'customer_payment_upload_id')) {
            Schema::table('refunds', function (Blueprint $table): void {
                $table->foreignId('customer_payment_upload_id')
                    ->nullable()
                    ->constrained('customer_payment_uploads')
                    ->nullOnDelete();
            });
        }

        if (! Schema::hasColumn('refunds', 'refund_account_head_id')) {
            Schema::table('refunds', function (Blueprint $table): void {
                $table->foreignId('refund_account_head_id')
                    ->nullable()
                    ->constrained('account_heads')
                    ->nullOnDelete();
            });
        }

        if (! Schema::hasColumn('refunds', 'receivable_account_head_id')) {
            Schema::table('refunds', function (Blueprint $table): void {
                $table->foreignId('receivable_account_head_id')
                    ->nullable()
                    ->constrained('account_heads')
                    ->nullOnDelete();
            });
        }

        if (! Schema::hasColumn('refunds', 'advance_account_head_id')) {
            Schema::table('refunds', function (Blueprint $table): void {
                $table->foreignId('advance_account_head_id')
                    ->nullable()
                    ->constrained('account_heads')
                    ->nullOnDelete();
            });
        }

        if (! Schema::hasColumn('refunds', 'account_voucher_id')) {
            Schema::table('refunds', function (Blueprint $table): void {
                $table->foreignId('account_voucher_id')
                    ->nullable()
                    ->constrained('account_vouchers')
                    ->nullOnDelete();
            });
        }

        if (! Schema::hasColumn('refunds', 'invoice_balance_reversed')) {
            Schema::table('refunds', function (Blueprint $table): void {
                $table->boolean('invoice_balance_reversed')
                    ->default(false);
            });
        }

        if (! Schema::hasColumn('refunds', 'approved_by')) {
            Schema::table('refunds', function (Blueprint $table): void {
                $table->foreignId('approved_by')
                    ->nullable()
                    ->constrained('users')
                    ->nullOnDelete();
            });
        }

        if (! Schema::hasColumn('refunds', 'approved_at')) {
            Schema::table('refunds', function (Blueprint $table): void {
                $table->timestamp('approved_at')
                    ->nullable();
            });
        }

        if (! Schema::hasColumn('refunds', 'rejected_by')) {
            Schema::table('refunds', function (Blueprint $table): void {
                $table->foreignId('rejected_by')
                    ->nullable()
                    ->constrained('users')
                    ->nullOnDelete();
            });
        }

        if (! Schema::hasColumn('refunds', 'rejected_at')) {
            Schema::table('refunds', function (Blueprint $table): void {
                $table->timestamp('rejected_at')
                    ->nullable();
            });
        }

        if (! Schema::hasColumn('refunds', 'rejection_reason')) {
            Schema::table('refunds', function (Blueprint $table): void {
                $table->text('rejection_reason')
                    ->nullable();
            });
        }

        if (! Schema::hasColumn('refunds', 'posted_by')) {
            Schema::table('refunds', function (Blueprint $table): void {
                $table->foreignId('posted_by')
                    ->nullable()
                    ->constrained('users')
                    ->nullOnDelete();
            });
        }

        if (! Schema::hasColumn('refunds', 'posted_at')) {
            Schema::table('refunds', function (Blueprint $table): void {
                $table->timestamp('posted_at')
                    ->nullable();
            });
        }

        if (! Schema::hasColumn('refunds', 'voided_by')) {
            Schema::table('refunds', function (Blueprint $table): void {
                $table->foreignId('voided_by')
                    ->nullable()
                    ->constrained('users')
                    ->nullOnDelete();
            });
        }

        if (! Schema::hasColumn('refunds', 'voided_at')) {
            Schema::table('refunds', function (Blueprint $table): void {
                $table->timestamp('voided_at')
                    ->nullable();
            });
        }

        if (! Schema::hasColumn('refunds', 'void_reason')) {
            Schema::table('refunds', function (Blueprint $table): void {
                $table->text('void_reason')
                    ->nullable();
            });
        }

        if (! Schema::hasColumn('refunds', 'created_by')) {
            Schema::table('refunds', function (Blueprint $table): void {
                $table->foreignId('created_by')
                    ->nullable()
                    ->constrained('users')
                    ->nullOnDelete();
            });
        }

        if (! Schema::hasColumn('refunds', 'notes')) {
            Schema::table('refunds', function (Blueprint $table): void {
                $table->text('notes')
                    ->nullable();
            });
        }
    }

    public function down(): void
    {
        $foreignColumns = [
            'invoice_id',
            'customer_payment_upload_id',
            'refund_account_head_id',
            'receivable_account_head_id',
            'advance_account_head_id',
            'account_voucher_id',
            'approved_by',
            'rejected_by',
            'posted_by',
            'voided_by',
            'created_by',
        ];

        foreach ($foreignColumns as $column) {
            if (Schema::hasColumn('refunds', $column)) {
                Schema::table('refunds', function (Blueprint $table) use ($column): void {
                    $table->dropConstrainedForeignId($column);
                });
            }
        }

        $normalColumns = [
            'refund_type',
            'invoice_balance_reversed',
            'approved_at',
            'rejected_at',
            'rejection_reason',
            'posted_at',
            'voided_at',
            'void_reason',
            'notes',
        ];

        foreach ($normalColumns as $column) {
            if (Schema::hasColumn('refunds', $column)) {
                Schema::table('refunds', function (Blueprint $table) use ($column): void {
                    $table->dropColumn($column);
                });
            }
        }
    }
};