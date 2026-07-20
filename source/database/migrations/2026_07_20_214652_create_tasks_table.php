<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tasks', function (Blueprint $table): void {
            $table->id();

            $table->string('title');
            $table->text('description')->nullable();

            $table->string('task_type', 50)
                ->default('General');

            $table->string('status', 30)
                ->default('Pending');

            $table->string('priority', 20)
                ->default('Normal');

            $table->foreignId('assigned_to_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->foreignId('created_by_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->foreignId('completed_by_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->foreignId('customer_id')
                ->nullable()
                ->constrained('customers')
                ->nullOnDelete();

            $table->nullableMorphs('related');

            $table->dateTime('starts_at')->nullable();
            $table->dateTime('due_at')->nullable();
            $table->dateTime('reminder_at')->nullable();
            $table->dateTime('completed_at')->nullable();

            $table->boolean('is_private')
                ->default(false);

            $table->text('completion_notes')->nullable();
            $table->json('metadata')->nullable();

            $table->timestamps();

            $table->index(['status', 'due_at']);
            $table->index(['assigned_to_id', 'status']);
            $table->index(['customer_id', 'status']);
            $table->index(['priority', 'due_at']);
            $table->index('reminder_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};