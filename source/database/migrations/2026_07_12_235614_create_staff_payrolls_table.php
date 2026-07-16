<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('staff_payrolls', function (Blueprint $table): void {
            $table->id();
            $table->string('payroll_no')->unique();
            $table->foreignId('staff_member_id')->constrained('staff_members')->cascadeOnDelete();
            $table->unsignedTinyInteger('salary_month');
            $table->unsignedSmallInteger('salary_year');
            $table->decimal('basic_salary', 15, 2)->default(0);
            $table->decimal('allowance', 15, 2)->default(0);
            $table->decimal('bonus', 15, 2)->default(0);
            $table->decimal('deduction', 15, 2)->default(0);
            $table->decimal('advance', 15, 2)->default(0);
            $table->decimal('net_salary', 15, 2)->default(0);
            $table->decimal('paid_amount', 15, 2)->default(0);
            $table->decimal('balance_amount', 15, 2)->default(0);
            $table->date('payment_date')->nullable();
            $table->string('payment_method')->nullable();
            $table->string('status')->default('Unpaid'); // Unpaid, Partial, Paid, Cancelled
            $table->text('remarks')->nullable();
            $table->timestamps();

            $table->unique(['staff_member_id', 'salary_month', 'salary_year']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('staff_payrolls');
    }
};