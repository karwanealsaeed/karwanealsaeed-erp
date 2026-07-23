<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('customers', 'agency_name')) {
            Schema::table('customers', function (Blueprint $table): void {
                $table
                    ->string('agency_name')
                    ->nullable();
            });
        }

        if (! Schema::hasColumn('customers', 'commission_rate')) {
            Schema::table('customers', function (Blueprint $table): void {
                $table
                    ->decimal('commission_rate', 5, 2)
                    ->nullable();
            });
        }

        if (! Schema::hasColumn('customers', 'credit_limit')) {
            Schema::table('customers', function (Blueprint $table): void {
                $table
                    ->decimal('credit_limit', 15, 2)
                    ->nullable();
            });
        }
    }

    public function down(): void
    {
        $columns = array_values(array_filter(
            [
                'agency_name',
                'commission_rate',
                'credit_limit',
            ],
            static fn (string $column): bool =>
                Schema::hasColumn('customers', $column),
        ));

        if ($columns !== []) {
            Schema::table('customers', function (Blueprint $table) use ($columns): void {
                $table->dropColumn($columns);
            });
        }
    }
};