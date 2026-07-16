<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('supplier_bills', function (Blueprint $table): void {
            $table->id();

            $table->string('bill_no', 50)
                ->nullable()
                ->unique();

            $table->foreignId('supplier_id')
                ->constrained('suppliers')
                ->restrictOnDelete();

            $table->string('supplier_invoice_no', 100)
                ->nullable()
                ->index();

            $table->date('bill_date')->index();
            $table->date('due_date')->nullable()->index();

            $table->string('service_type', 50)
                ->default('Other')
                ->index();

            $table->string('reference_no', 100)
                ->nullable()
                ->index();

            $table->text('description')->nullable();

            $table->decimal('sub_total', 15, 2)->default(0);
            $table->decimal('discount', 15, 2)->default(0);
            $table->decimal('tax', 15, 2)->default(0);

            $table->decimal('total_amount', 15, 2)->default(0);
            $table->decimal('paid_amount', 15, 2)->default(0);
            $table->decimal('balance_amount', 15, 2)->default(0);

            $table->string('status', 30)
                ->default('Draft')
                ->index();

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
                ['supplier_id', 'bill_date'],
                'supplier_bills_supplier_date_index'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('supplier_bills');
    }
};