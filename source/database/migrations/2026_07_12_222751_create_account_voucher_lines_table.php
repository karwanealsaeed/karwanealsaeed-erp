<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('account_voucher_lines', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('account_voucher_id')->constrained('account_vouchers')->cascadeOnDelete();
            $table->foreignId('account_head_id')->constrained('account_heads')->cascadeOnDelete();
            $table->text('description')->nullable();
            $table->decimal('debit', 15, 2)->default(0);
            $table->decimal('credit', 15, 2)->default(0);
            $table->unsignedInteger('sort_order')->default(1);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('account_voucher_lines');
    }
};