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
        Schema::create('visa_applications', function (Blueprint $table) {
            $table->id();

            $table->string('application_no')->unique();

            $table->foreignId('customer_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('country_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('visa_type_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('passport_no');

            $table->date('travel_date')->nullable();
            $table->date('return_date')->nullable();

            $table->decimal('visa_fee', 10, 2)->default(0);
            $table->decimal('service_fee', 10, 2)->default(0);
            $table->decimal('total_amount', 10, 2)->default(0);
            $table->decimal('paid_amount', 10, 2)->default(0);
            $table->decimal('balance_amount', 10, 2)->default(0);

            $table->enum('status', [
                'Draft',
                'Applied',
                'Processing',
                'Approved',
                'Rejected',
                'Delivered',
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
        Schema::dropIfExists('visa_applications');
    }
};