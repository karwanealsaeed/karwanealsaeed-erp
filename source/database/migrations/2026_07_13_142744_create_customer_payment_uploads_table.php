<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customer_payment_uploads', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
            $table->string('upload_no')->unique();
            $table->string('service_type')->nullable();
            $table->string('reference_no')->nullable();
            $table->decimal('amount', 15, 2)->default(0);
            $table->string('payment_method')->nullable();
            $table->date('payment_date')->nullable();
            $table->string('transaction_id')->nullable();
            $table->string('receipt_file')->nullable();
            $table->string('status')->default('Pending'); // Pending, Approved, Rejected
            $table->text('customer_notes')->nullable();
            $table->text('admin_notes')->nullable();
            $table->timestamps();

            $table->index(['customer_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_payment_uploads');
    }
};