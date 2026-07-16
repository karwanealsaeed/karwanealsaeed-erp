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
        Schema::create('hotel_payments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('hotel_booking_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->date('payment_date');

            $table->decimal('amount', 12, 2)->default(0);

            $table->enum('payment_method', [
                'Cash',
                'Bank',
                'JazzCash',
                'EasyPaisa',
                'Credit Card',
                'Cheque',
            ])->default('Cash');

            $table->string('reference_no')->nullable();

            $table->text('notes')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hotel_payments');
    }
};