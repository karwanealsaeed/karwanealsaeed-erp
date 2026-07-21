<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

return new class extends Migration
{
    public function up(): void
    {
        if (
            ! Schema::hasTable(
                'customer_payment_uploads',
            )
        ) {
            return;
        }

        DB::table(
            'customer_payment_uploads',
        )
            ->select([
                'id',
                'receipt_file',
            ])
            ->whereNotNull(
                'receipt_file',
            )
            ->orderBy(
                'id',
            )
            ->chunkById(
                100,
                function ($receipts): void {
                    foreach ($receipts as $receipt) {
                        $path =
                            trim(
                                str_replace(
                                    '\\',
                                    '/',
                                    (string) $receipt
                                        ->receipt_file,
                                ),
                            );

                        if (
                            $path === ''
                            || str_contains(
                                $path,
                                "\0",
                            )
                            || str_contains(
                                $path,
                                '..',
                            )
                            || str_starts_with(
                                $path,
                                '/',
                            )
                        ) {
                            continue;
                        }

                        if (
                            Storage::disk(
                                'local',
                            )->exists(
                                $path,
                            )
                        ) {
                            continue;
                        }

                        if (
                            ! Storage::disk(
                                'public',
                            )->exists(
                                $path,
                            )
                        ) {
                            continue;
                        }

                        $sourceStream =
                            Storage::disk(
                                'public',
                            )->readStream(
                                $path,
                            );

                        if (
                            ! is_resource(
                                $sourceStream,
                            )
                        ) {
                            throw new RuntimeException(
                                "Receipt {$receipt->id} read nahi hui.",
                            );
                        }

                        try {
                            $written =
                                Storage::disk(
                                    'local',
                                )->writeStream(
                                    $path,
                                    $sourceStream,
                                );
                        } finally {
                            fclose(
                                $sourceStream,
                            );
                        }

                        if (
                            ! $written
                            || ! Storage::disk(
                                'local',
                            )->exists(
                                $path,
                            )
                        ) {
                            throw new RuntimeException(
                                "Receipt {$receipt->id} private storage mein move nahi hui.",
                            );
                        }

                        $publicSize =
                            Storage::disk(
                                'public',
                            )->size(
                                $path,
                            );

                        $privateSize =
                            Storage::disk(
                                'local',
                            )->size(
                                $path,
                            );

                        if ($publicSize !== $privateSize) {
                            Storage::disk(
                                'local',
                            )->delete(
                                $path,
                            );

                            throw new RuntimeException(
                                "Receipt {$receipt->id} size verification fail hui.",
                            );
                        }

                        Storage::disk(
                            'public',
                        )->delete(
                            $path,
                        );
                    }
                },
                'id',
            );
    }

    public function down(): void
    {
        /*
         * Private payment receipts ko public storage mein
         * wapas expose nahi kiya jayega.
         */
    }
};