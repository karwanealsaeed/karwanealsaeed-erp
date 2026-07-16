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
        Schema::create('hotel_bookings', function (Blueprint $table) {
            $table->id();

            $table->string('booking_no')->unique();

            $table->foreignId('customer_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('country_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('city');

            $table->string('hotel_name');

            $table->date('check_in_date');

            $table->date('check_out_date')->nullable();

            $table->integer('no_of_rooms')->default(1);

            $table->integer('no_of_persons')->default(1);

            $table->string('room_type')->nullable();

            $table->decimal('total_amount', 12, 2)->default(0);

            $table->decimal('paid_amount', 12, 2)->default(0);

            $table->decimal('balance_amount', 12, 2)->default(0);

            $table->enum('status', [
                'Draft',
                'Booked',
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
        Schema::dropIfExists('hotel_bookings');
    }
};