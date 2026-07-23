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

    private function next(
        string $sequenceKey,
        string $prefix,
        string $table,
        string $column,
    ): string {
        return DB::transaction(
            function () use (
                $sequenceKey,
                $prefix,
                $table,
                $column,
            ): string {
                DB::table('master_code_sequences')
                    ->insertOrIgnore([
                        'sequence_key' => $sequenceKey,
                        'next_value' =>
                            $this->initialNextValue(
                                $prefix,
                                $table,
                                $column,
                            ),
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

                $number = (int) $sequence->next_value;

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
                    '%s-%06d',
                    $prefix,
                    $number,
                );
            },
            attempts: 5,
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
