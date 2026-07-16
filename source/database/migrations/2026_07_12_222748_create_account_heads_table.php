<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('account_heads', function (Blueprint $table): void {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->string('type'); // Asset, Liability, Equity, Income, Expense
            $table->string('sub_type')->nullable(); // Cash, Bank, Customer, Supplier, Sales, Expense etc
            $table->foreignId('parent_id')->nullable()->constrained('account_heads')->nullOnDelete();
            $table->decimal('opening_debit', 15, 2)->default(0);
            $table->decimal('opening_credit', 15, 2)->default(0);
            $table->string('status')->default('Active');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('account_heads');
    }
};