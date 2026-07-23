<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table(
            'account_vouchers',
            function (Blueprint $table): void {
                if (
                    ! Schema::hasColumn(
                        'account_vouchers',
                        'reversal_of_voucher_id',
                    )
                ) {
                    $table
                        ->foreignId(
                            'reversal_of_voucher_id',
                        )
                        ->nullable()
                        ->unique()
                        ->after('source_no')
                        ->constrained(
                            'account_vouchers',
                        )
                        ->restrictOnDelete();
                }

                if (
                    ! Schema::hasColumn(
                        'account_vouchers',
                        'reversal_reason',
                    )
                ) {
                    $table
                        ->text('reversal_reason')
                        ->nullable()
                        ->after(
                            'reversal_of_voucher_id',
                        );
                }
            },
        );
    }

    public function down(): void
    {
        Schema::table(
            'account_vouchers',
            function (Blueprint $table): void {
                if (
                    Schema::hasColumn(
                        'account_vouchers',
                        'reversal_of_voucher_id',
                    )
                ) {
                    $table->dropForeign([
                        'reversal_of_voucher_id',
                    ]);

                    $table->dropUnique([
                        'reversal_of_voucher_id',
                    ]);
                }

                $columns = [];

                foreach (
                    [
                        'reversal_reason',
                        'reversal_of_voucher_id',
                    ]
                    as $column
                ) {
                    if (
                        Schema::hasColumn(
                            'account_vouchers',
                            $column,
                        )
                    ) {
                        $columns[] = $column;
                    }
                }

                if ($columns !== []) {
                    $table->dropColumn($columns);
                }
            },
        );
    }
};