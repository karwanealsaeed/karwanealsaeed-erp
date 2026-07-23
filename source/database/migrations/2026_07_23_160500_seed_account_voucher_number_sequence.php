<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (
            ! Schema::hasTable('master_code_sequences')
            || ! Schema::hasTable('account_vouchers')
        ) {
            return;
        }

        DB::transaction(function (): void {
            $maxId =
                (int)
                    DB::table('account_vouchers')
                        ->max('id');

            $maxSuffix = 0;

            DB::table('account_vouchers')
                ->where(
                    'voucher_no',
                    'like',
                    'AV-%',
                )
                ->pluck('voucher_no')
                ->each(
                    static function (
                        mixed $voucherNo
                    ) use (&$maxSuffix): void {
                        if (
                            is_string($voucherNo)
                            && preg_match(
                                '/^AV-(\d+)$/',
                                $voucherNo,
                                $matches,
                            ) === 1
                        ) {
                            $maxSuffix =
                                max(
                                    $maxSuffix,
                                    (int) $matches[1],
                                );
                        }
                    },
                );

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
                // max(id) and voucher suffix remain safe fallbacks.
            }

            $safeNext =
                max(
                    1,
                    $maxId + 1,
                    $maxSuffix + 1,
                    $autoIncrement,
                );

            $existing =
                DB::table('master_code_sequences')
                    ->where(
                        'sequence_key',
                        'account_voucher',
                    )
                    ->lockForUpdate()
                    ->first();

            if (! is_object($existing)) {
                DB::table('master_code_sequences')
                    ->insert([
                        'sequence_key' =>
                            'account_voucher',
                        'next_value' =>
                            $safeNext,
                        'created_at' =>
                            now(),
                        'updated_at' =>
                            now(),
                    ]);

                return;
            }

            if (
                (int) $existing->next_value
                < $safeNext
            ) {
                DB::table('master_code_sequences')
                    ->where(
                        'sequence_key',
                        'account_voucher',
                    )
                    ->update([
                        'next_value' =>
                            $safeNext,
                        'updated_at' =>
                            now(),
                    ]);
            }
        });
    }

    public function down(): void
    {
        if (
            Schema::hasTable(
                'master_code_sequences',
            )
        ) {
            DB::table(
                'master_code_sequences',
            )
                ->where(
                    'sequence_key',
                    'account_voucher',
                )
                ->delete();
        }
    }
};