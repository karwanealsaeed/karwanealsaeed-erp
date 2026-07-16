<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (
            Schema::hasColumn(
                'customers',
                'customer_type'
            )
            && DB::getDriverName() === 'mysql'
        ) {
            DB::statement(
                "ALTER TABLE customers
                MODIFY customer_type VARCHAR(50)
                NOT NULL DEFAULT 'Customer'"
            );
        }

        if (Schema::hasTable('customers')) {
            DB::table('customers')
                ->where(
                    'customer_type',
                    'Regular'
                )
                ->update([
                    'customer_type' => 'Customer',
                ]);
        }
    }

    public function down(): void
    {
        if (
            Schema::hasColumn(
                'customers',
                'customer_type'
            )
            && DB::getDriverName() === 'mysql'
        ) {
            DB::statement(
                "ALTER TABLE customers
                MODIFY customer_type VARCHAR(50)
                NOT NULL DEFAULT 'Customer'"
            );
        }
    }
};