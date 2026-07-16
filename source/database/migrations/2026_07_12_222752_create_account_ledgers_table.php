<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('account_ledgers', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('account_head_id')->constrained('account_heads')->cascadeOnDelete();
            $table->foreignId('account_voucher_id')->nullable()->constrained('account_vouchers')->cascadeOnDelete();
            $table->foreignId('account_voucher_line_id')->nullable()->constrained('account_voucher_lines')->cascadeOnDelete();
            $table->date('entry_date');
            $table->string('voucher_no')->nullable();
            $table->string('voucher_type')->nullable();
            $table->string('reference_no')->nullable();
            $table->text('description')->nullable();
            $table->decimal('debit', 15, 2)->default(0);
            $table->decimal('credit', 15, 2)->default(0);
            $table->string('status')->default('Posted');
            $table->timestamps();

            $table->index(['account_head_id', 'entry_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('account_ledgers');
    }
};