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
        Schema::create('flight_bookings', function (Blueprint $table) {
            $table->id();

$table->string('booking_no')->unique();

$table->foreignId('customer_id')->constrained()->cascadeOnDelete();

$table->foreignId('airline_id')->constrained()->cascadeOnDelete();

$table->foreignId('from_airport_id')->constrained('airports')->cascadeOnDelete();

$table->foreignId('to_airport_id')->constrained('airports')->cascadeOnDelete();

$table->string('pnr')->nullable();

$table->string('ticket_no')->nullable();

$table->date('departure_date');

$table->date('return_date')->nullable();

$table->enum('trip_type', [
    'One Way',
    'Round Trip',
]);

$table->enum('class', [
    'Economy',
    'Premium Economy',
    'Business',
    'First',
]);

$table->decimal('fare',12,2)->default(0);

$table->decimal('tax',12,2)->default(0);

$table->decimal('service_fee',12,2)->default(0);

$table->decimal('total_amount',12,2)->default(0);

$table->decimal('paid_amount',12,2)->default(0);

$table->decimal('balance_amount',12,2)->default(0);

$table->enum('status',[
    'Draft',
    'Booked',
    'Ticket Issued',
    'Cancelled',
    'Refunded'
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
        Schema::dropIfExists('flight_bookings');
    }
};
