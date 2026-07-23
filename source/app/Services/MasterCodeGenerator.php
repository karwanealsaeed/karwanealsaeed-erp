<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\DB;
use RuntimeException;

final class MasterCodeGenerator
{
    public function nextCustomerCode(): string
    {
        return $this->next(
            sequenceKey: 'customer',
            prefix: 'CUS',
            table: 'customers',
            column: 'customer_code',
        );
    }

    public function nextSupplierCode(): string
    {
        return $this->next(
            sequenceKey: 'supplier',
            prefix: 'SUP',
            table: 'suppliers',
            column: 'supplier_code',
        );
    }

    public function nextHotelCode(): string
    {
        return $this->next(
            sequenceKey: 'hotel',
            prefix: 'HTL',
            table: 'hotels',
            column: 'hotel_code',
        );
    }

    public function nextAccountVoucherNumber(): string
    {
        return $this->next(
            sequenceKey: 'account_voucher',
            prefix: 'AV',
            table: 'account_vouchers',
            column: 'voucher_no',
            padding: 5,
            minimumNextValue:
                $this->accountVoucherMinimumNextValue(),
        );
    }
    private function next(
        string $sequenceKey,
        string $prefix,
        string $table,
        string $column,
        int $padding = 6,
        ?int $minimumNextValue = null,
    ): string {
        return DB::transaction(
            function () use (
                $sequenceKey,
                $prefix,
                $table,
                $column,
                $padding,
                $minimumNextValue,
            ): string {
                if ($padding < 1) {
                    throw new RuntimeException(
                        "Master code padding is invalid: {$sequenceKey}",
                    );
                }

                $initialNextValue = max(
                    $this->initialNextValue(
                        $prefix,
                        $table,
                        $column,
                    ),
                    $minimumNextValue ?? 1,
                );

                DB::table('master_code_sequences')
                    ->insertOrIgnore([
                        'sequence_key' => $sequenceKey,
                        'next_value' =>
                            $initialNextValue,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                $sequence =
                    DB::table('master_code_sequences')
                        ->where(
                            'sequence_key',
                            $sequenceKey,
                        )
                        ->lockForUpdate()
                        ->first();

                if (! is_object($sequence)) {
                    throw new RuntimeException(
                        "Master code sequence is unavailable: {$sequenceKey}",
                    );
                }

                $number = max(
                    (int) $sequence->next_value,
                    $initialNextValue,
                );

                if ($number < 1) {
                    throw new RuntimeException(
                        "Master code sequence is invalid: {$sequenceKey}",
                    );
                }

                DB::table('master_code_sequences')
                    ->where(
                        'sequence_key',
                        $sequenceKey,
                    )
                    ->update([
                        'next_value' => $number + 1,
                        'updated_at' => now(),
                    ]);

                return sprintf(
                    '%s-%0'.$padding.'d',
                    $prefix,
                    $number,
                );
            },
            attempts: 5,
        );
    }

    private function accountVoucherMinimumNextValue(): int
    {
        $maxId =
            (int)
                DB::table('account_vouchers')
                    ->max('id');

        $autoIncrement = $maxId + 1;
        $driver = DB::getDriverName();

        try {
            if ($driver === 'mysql') {
                $autoIncrement =
                    max(
                        $autoIncrement,
                        (int)
                            DB::table(
                                'information_schema.TABLES',
                            )
                                ->where(
                                    'TABLE_SCHEMA',
                                    DB::connection()
                                        ->getDatabaseName(),
                                )
                                ->where(
                                    'TABLE_NAME',
                                    'account_vouchers',
                                )
                                ->value(
                                    'AUTO_INCREMENT',
                                ),
                    );
            } elseif ($driver === 'sqlite') {
                $sequence =
                    DB::table('sqlite_sequence')
                        ->where(
                            'name',
                            'account_vouchers',
                        )
                        ->value('seq');

                if (is_numeric($sequence)) {
                    $autoIncrement =
                        max(
                            $autoIncrement,
                            ((int) $sequence) + 1,
                        );
                }
            }
        } catch (\Throwable) {
            // max(id) + 1 remains the safe fallback.
        }

        return max(
            1,
            $maxId + 1,
            $autoIncrement,
        );
    }
    private function initialNextValue(
        string $prefix,
        string $table,
        string $column,
    ): int {
        $latest =
            DB::table($table)
                ->where(
                    $column,
                    'like',
                    $prefix.'-%',
                )
                ->orderByDesc($column)
                ->value($column);

        if (
            ! is_string($latest)
            || ! preg_match(
                '/(\d+)$/',
                $latest,
                $matches,
            )
        ) {
            return 1;
        }

        return ((int) $matches[1]) + 1;
    }
}
