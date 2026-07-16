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
        Schema::create('umrah_bookings', function (Blueprint $table) {
            $table->id();

            $table->string('booking_no')->unique();

            $table->foreignId('customer_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('package_name');

            $table->date('travel_date');

            $table->date('return_date')->nullable();

            $table->string('makkah_hotel')->nullable();

            $table->string('madinah_hotel')->nullable();

            $table->integer('no_of_persons')->default(1);

            $table->decimal('package_amount', 12, 2)->default(0);

            $table->decimal('paid_amount', 12, 2)->default(0);

            $table->decimal('balance_amount', 12, 2)->default(0);

            $table->enum('status', [
                'Draft',
                'Booked',
                'Processing',
                'Confirmed',
                'Cancelled',
                'Completed',
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
        Schema::dropIfExists('umrah_bookings');
    }
};