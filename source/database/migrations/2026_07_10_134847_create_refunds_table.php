<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('refunds', function (Blueprint $table) {
            $table->id();

            $table->string('refund_no')->unique();

            $table->foreignId('customer_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->date('refund_date');

            $table->enum('service_type', [
                'Visa',
                'Flight',
                'Umrah',
                'Hotel',
                'Other',
            ])->default('Other');

            $table->string('service_reference')->nullable();

            $table->decimal('refund_amount', 12, 2)->default(0);

            $table->enum('payment_method', [
                'Cash',
                'Bank',
                'JazzCash',
                'EasyPaisa',
                'Credit Card',
                'Cheque',
            ])->default('Cash');

            $table->string('reference_no')->nullable();

            $table->enum('status', [
                'Pending',
                'Approved',
                'Paid',
                'Rejected',
                'Cancelled',
            ])->default('Pending');

            $table->text('reason')->nullable();
            $table->text('notes')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('refunds');
    }
};