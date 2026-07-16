<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('staff_leaves', function (Blueprint $table): void {
            $table->id();

            $table->string('leave_no', 50)
                ->nullable()
                ->unique();

            $table->foreignId('staff_id')
                ->constrained('staff')
                ->cascadeOnDelete();

            $table->foreignId('leave_type_id')
                ->constrained('leave_types')
                ->restrictOnDelete();

            $table->date('start_date');
            $table->date('end_date');

            $table->string('session', 30)
                ->default('Full Day');

            $table->decimal('total_days', 8, 2)
                ->default(1);

            $table->text('reason');
            $table->string('attachment')->nullable();

            $table->string('status', 30)
                ->default('Pending')
                ->index();

            $table->timestamp('applied_at')->nullable();

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
            $table->text('admin_notes')->nullable();

            $table->timestamps();

            $table->index(
                ['staff_id', 'start_date', 'end_date'],
                'staff_leave_dates_index'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('staff_leaves');
    }
};