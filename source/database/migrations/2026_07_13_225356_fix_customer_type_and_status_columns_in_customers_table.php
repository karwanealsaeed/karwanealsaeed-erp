<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('customers')) {
            return;
        }

        if (Schema::hasColumn('customers', 'status')) {
            DB::table('customers')
                ->whereNull('status')
                ->update(['status' => 1]);
        }

        /*
         * MySQL/MariaDB-specific column modifications.
         * SQLite uses dynamic typing during automated tests.
         */
        if (DB::getDriverName() === 'mysql') {
            if (Schema::hasColumn('customers', 'customer_type')) {
                DB::statement("
                    ALTER TABLE `customers`
                    MODIFY `customer_type` VARCHAR(50)
                    NOT NULL DEFAULT 'Customer'
                ");
            }

            if (Schema::hasColumn('customers', 'status')) {
                DB::statement("
                    ALTER TABLE `customers`
                    MODIFY `status` VARCHAR(30)
                    NOT NULL DEFAULT 'Active'
                ");
            }
        }

        if (Schema::hasColumn('customers', 'customer_type')) {
            DB::table('customers')
                ->whereIn('customer_type', [
                    'Regular',
                    'Individual',
                    'regular',
                ])
                ->update([
                    'customer_type' => 'Customer',
                ]);

            DB::table('customers')
                ->where(function ($query): void {
                    $query->whereNull('customer_type')
                        ->orWhere('customer_type', '');
                })
                ->update([
                    'customer_type' => 'Customer',
                ]);
        }

        if (Schema::hasColumn('customers', 'status')) {
            DB::table('customers')
                ->whereIn('status', [
                    '1',
                    'true',
                    'Yes',
                    'yes',
                ])
                ->update([
                    'status' => 'Active',
                ]);

            DB::table('customers')
                ->whereIn('status', [
                    '0',
                    'false',
                    'No',
                    'no',
                ])
                ->update([
                    'status' => 'Inactive',
                ]);

            DB::table('customers')
                ->where(function ($query): void {
                    $query->whereNull('status')
                        ->orWhere('status', '');
                })
                ->update([
                    'status' => 'Active',
                ]);
        }
    }

    public function down(): void
    {
        /*
         * Text columns intentionally retained to prevent data loss.
         */
    }
};