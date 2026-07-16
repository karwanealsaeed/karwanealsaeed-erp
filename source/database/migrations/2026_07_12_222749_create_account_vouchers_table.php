<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('account_vouchers', function (Blueprint $table): void {
            $table->id();
            $table->string('voucher_no')->unique();
            $table->date('voucher_date');
            $table->string('voucher_type'); // Receipt, Payment, Journal, Contra
            $table->string('reference_no')->nullable();
            $table->string('party_name')->nullable();
            $table->text('description')->nullable();
            $table->decimal('total_debit', 15, 2)->default(0);
            $table->decimal('total_credit', 15, 2)->default(0);
            $table->string('status')->default('Posted'); // Draft, Posted, Cancelled
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('account_vouchers');
    }
};