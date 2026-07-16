<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('accounting_period_locks')) {
            return;
        }

        Schema::create(
            'accounting_period_locks',
            function (Blueprint $table): void {
                $table->id();

                $table->string('name');

                $table->date('period_start');
                $table->date('period_end');

                $table->string(
                    'lock_scope',
                    50
                )
                    ->default('All Accounting')
                    ->index();

                $table->string(
                    'status',
                    20
                )
                    ->default('Open')
                    ->index();

                $table->foreignId('created_by')
                    ->nullable()
                    ->constrained('users')
                    ->nullOnDelete();

                $table->foreignId('locked_by')
                    ->nullable()
                    ->constrained('users')
                    ->nullOnDelete();

                $table->timestamp('locked_at')
                    ->nullable();

                $table->foreignId('unlocked_by')
                    ->nullable()
                    ->constrained('users')
                    ->nullOnDelete();

                $table->timestamp('unlocked_at')
                    ->nullable();

                $table->text('unlock_reason')
                    ->nullable();

                $table->text('notes')
                    ->nullable();

                $table->timestamps();

                $table->index([
                    'period_start',
                    'period_end',
                ]);
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'accounting_period_locks'
        );
    }
};