<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(
            'master_code_sequences',
            function (Blueprint $table): void {
                $table->string('sequence_key', 64)->primary();
                $table->unsignedBigInteger('next_value');
                $table->timestamps();
            },
        );

        Schema::table(
            'customers',
            function (Blueprint $table): void {
                $table
                    ->string('customer_code', 32)
                    ->nullable()
                    ->after('id');
            },
        );

        Schema::table(
            'suppliers',
            function (Blueprint $table): void {
                $table
                    ->string('supplier_code', 32)
                    ->nullable()
                    ->after('id');
            },
        );

        $customerNumber = 1;

        DB::table('customers')
            ->select('id')
            ->orderBy('id')
            ->chunkById(
                250,
                function ($rows) use (&$customerNumber): void {
                    foreach ($rows as $row) {
                        DB::table('customers')
                            ->where('id', $row->id)
                            ->update([
                                'customer_code' =>
                                    sprintf(
                                        'CUS-%06d',
                                        $customerNumber,
                                    ),
                            ]);

                        $customerNumber++;
                    }
                },
            );

        $supplierNumber = 1;

        DB::table('suppliers')
            ->select('id')
            ->orderBy('id')
            ->chunkById(
                250,
                function ($rows) use (&$supplierNumber): void {
                    foreach ($rows as $row) {
                        DB::table('suppliers')
                            ->where('id', $row->id)
                            ->update([
                                'supplier_code' =>
                                    sprintf(
                                        'SUP-%06d',
                                        $supplierNumber,
                                    ),
                            ]);

                        $supplierNumber++;
                    }
                },
            );

        $now = now();

        DB::table('master_code_sequences')->insert([
            [
                'sequence_key' => 'customer',
                'next_value' => $customerNumber,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'sequence_key' => 'supplier',
                'next_value' => $supplierNumber,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);

        Schema::table(
            'customers',
            function (Blueprint $table): void {
                $table->unique(
                    'customer_code',
                    'customers_customer_code_unique',
                );
            },
        );

        Schema::table(
            'suppliers',
            function (Blueprint $table): void {
                $table->unique(
                    'supplier_code',
                    'suppliers_supplier_code_unique',
                );
            },
        );

        $this->enforceNotNull(
            'customers',
            'customer_code',
        );

        $this->enforceNotNull(
            'suppliers',
            'supplier_code',
        );
    }

    public function down(): void
    {
        Schema::table(
            'customers',
            function (Blueprint $table): void {
                $table->dropUnique(
                    'customers_customer_code_unique',
                );
                $table->dropColumn('customer_code');
            },
        );

        Schema::table(
            'suppliers',
            function (Blueprint $table): void {
                $table->dropUnique(
                    'suppliers_supplier_code_unique',
                );
                $table->dropColumn('supplier_code');
            },
        );

        Schema::dropIfExists('master_code_sequences');
    }

    private function enforceNotNull(
        string $table,
        string $column,
    ): void {
        if (DB::getDriverName() === 'mysql') {
            DB::statement(
                sprintf(
                    'ALTER TABLE `%s` MODIFY `%s` VARCHAR(32) NOT NULL',
                    $table,
                    $column,
                ),
            );

            return;
        }

        Schema::table(
            $table,
            function (Blueprint $blueprint) use ($column): void {
                $blueprint
                    ->string($column, 32)
                    ->nullable(false)
                    ->change();
            },
        );
    }
};
