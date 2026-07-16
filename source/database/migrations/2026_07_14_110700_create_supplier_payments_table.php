<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('supplier_payments', function (Blueprint $table): void {
            $table->id();

            $table->string('payment_no', 50)
                ->nullable()
                ->unique();

            $table->foreignId('supplier_bill_id')
                ->constrained('supplier_bills')
                ->restrictOnDelete();

            $table->foreignId('supplier_id')
                ->constrained('suppliers')
                ->restrictOnDelete();

            $table->date('payment_date')->index();

            $table->decimal('amount', 15, 2);

            $table->string('payment_method', 50)
                ->default('Cash');

            $table->string('reference_no', 100)
                ->nullable()
                ->index();

            $table->string('status', 30)
                ->default('Pending')
                ->index();

            $table->foreignId('approved_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('approved_at')->nullable();

            $table->foreignId('rejected_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('rejected_at')->nullable();
            $table->text('rejection_reason')->nullable();

            $table->foreignId('account_voucher_id')
                ->nullable()
                ->constrained('account_vouchers')
                ->nullOnDelete();

            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->text('notes')->nullable();

            $table->timestamps();

            $table->index(
                ['supplier_id', 'payment_date'],
                'supplier_payments_supplier_date_index'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('supplier_payments');
    }
};