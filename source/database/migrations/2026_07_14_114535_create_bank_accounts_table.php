<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bank_accounts', function (Blueprint $table): void {
            $table->id();

            $table->foreignId('account_head_id')
                ->unique()
                ->constrained('account_heads')
                ->restrictOnDelete();

            $table->string('name');
            $table->string('bank_name');
            $table->string('account_title')->nullable();
            $table->string('account_number')->nullable();
            $table->string('iban')->nullable();
            $table->string('branch')->nullable();

            $table->string('currency', 10)
                ->default('PKR');

            /*
             * Bank statement opening balance.
             * Ye opening_date ke start ka balance hoga.
             */
            $table->date('opening_date')->nullable();

            $table->decimal('opening_balance', 15, 2)
                ->default(0);

            $table->string('status', 30)
                ->default('Active')
                ->index();

            $table->text('notes')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bank_accounts');
    }
};