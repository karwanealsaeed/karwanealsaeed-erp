<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('staff_ledger_entries', function (Blueprint $table): void {
            $table->id();

            $table->string('entry_no', 50)
                ->nullable()
                ->unique();

            $table->foreignId('staff_id')
                ->constrained('staff')
                ->cascadeOnDelete();

            $table->date('entry_date')->index();

            $table->string('category', 50)->index();

            $table->string('reference_no', 100)->nullable();
            $table->string('reference_type', 100)->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();

            /*
             * Staff perspective:
             * Debit  = staff se recoverable amount / advance / loan
             * Credit = salary / payment / bonus / reimbursement
             */
            $table->decimal('debit', 15, 2)->default(0);
            $table->decimal('credit', 15, 2)->default(0);

            $table->text('description')->nullable();
            $table->text('notes')->nullable();

            $table->string('status', 30)
                ->default('Pending')
                ->index();

            $table->foreignId('approved_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('approved_at')->nullable();

            $table->foreignId('rejected_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('rejected_at')->nullable();
            $table->text('rejection_reason')->nullable();

            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();

            $table->index(
                ['staff_id', 'entry_date'],
                'staff_ledger_staff_date_index'
            );

            $table->index(
                ['reference_type', 'reference_id'],
                'staff_ledger_reference_index'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('staff_ledger_entries');
    }
};