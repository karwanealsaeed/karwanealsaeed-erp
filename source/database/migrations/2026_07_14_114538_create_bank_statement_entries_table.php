<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(
            'bank_statement_entries',
            function (Blueprint $table): void {
                $table->id();

                $table->string('entry_no', 50)
                    ->nullable()
                    ->unique();

                $table->foreignId('bank_account_id')
                    ->constrained('bank_accounts')
                    ->restrictOnDelete();

                $table->date('statement_date')->index();

                $table->string('reference_no', 150)
                    ->nullable()
                    ->index();

                $table->string('cheque_no', 100)
                    ->nullable();

                $table->text('description')->nullable();

                /*
                 * Bank statement perspective:
                 * Deposit    = money received in bank
                 * Withdrawal = money paid from bank
                 */
                $table->decimal('deposit', 15, 2)
                    ->default(0);

                $table->decimal('withdrawal', 15, 2)
                    ->default(0);

                $table->decimal('running_balance', 15, 2)
                    ->nullable();

                $table->string(
                    'reconciliation_status',
                    30
                )
                    ->default('Unmatched')
                    ->index();

                $table->foreignId(
                    'matched_account_ledger_id'
                )
                    ->nullable()
                    ->unique()
                    ->constrained('account_ledgers')
                    ->nullOnDelete();

                $table->foreignId('matched_by')
                    ->nullable()
                    ->constrained('users')
                    ->nullOnDelete();

                $table->timestamp('matched_at')->nullable();

                $table->foreignId('created_by')
                    ->nullable()
                    ->constrained('users')
                    ->nullOnDelete();

                $table->text('notes')->nullable();

                $table->timestamps();

                $table->index(
                    ['bank_account_id', 'statement_date'],
                    'bank_statement_account_date_index'
                );
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'bank_statement_entries'
        );
    }
};