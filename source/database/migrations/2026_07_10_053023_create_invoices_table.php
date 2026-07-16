<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();

            $table->string('invoice_no')->unique();

            $table->foreignId('customer_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->date('invoice_date');

            $table->date('due_date')->nullable();

            $table->enum('service_type', [
                'Visa',
                'Flight',
                'Umrah',
                'Hotel',
                'Other',
            ])->default('Other');

            $table->string('service_reference')->nullable();

            $table->decimal('sub_total', 12, 2)->default(0);

            $table->decimal('discount', 12, 2)->default(0);

            $table->decimal('tax', 12, 2)->default(0);

            $table->decimal('total_amount', 12, 2)->default(0);

            $table->decimal('paid_amount', 12, 2)->default(0);

            $table->decimal('balance_amount', 12, 2)->default(0);

            $table->enum('status', [
                'Draft',
                'Unpaid',
                'Partially Paid',
                'Paid',
                'Cancelled',
            ])->default('Draft');

            $table->text('notes')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};