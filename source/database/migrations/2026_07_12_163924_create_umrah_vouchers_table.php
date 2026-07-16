<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('umrah_vouchers', function (Blueprint $table): void {
            $table->id();

            $table->string('voucher_no')->unique();

            $table->foreignId('customer_id')
                ->nullable()
                ->constrained('customers')
                ->nullOnDelete();

            $table->foreignId('umrah_booking_id')
                ->nullable()
                ->constrained('umrah_bookings')
                ->nullOnDelete();

            $table->foreignId('umrah_package_id')
                ->nullable()
                ->constrained('umrah_packages')
                ->nullOnDelete();

            $table->string('lead_guest')->nullable();
            $table->string('phone')->nullable();

            $table->unsignedInteger('adults')->default(1);
            $table->unsignedInteger('children')->default(0);
            $table->unsignedInteger('infants')->default(0);

            $table->json('passengers')->nullable();

            $table->string('room_type')->nullable();
            $table->unsignedInteger('room_count')->default(1);

            $table->string('pnr')->nullable();
            $table->string('booking_reference')->nullable();

            $table->text('remarks')->nullable();
            $table->text('special_instructions')->nullable();
            $table->text('terms_conditions')->nullable();

            $table->string('status')->default('Issued');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('umrah_vouchers');
    }
};