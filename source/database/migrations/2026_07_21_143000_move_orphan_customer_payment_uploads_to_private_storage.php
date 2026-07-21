<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\Storage;

return new class extends Migration
{
    /**
     * Move every legacy or orphan payment receipt out of the
     * publicly linked filesystem.
     */
    public function up(): void
    {
        /*
         * Filesystem migrations must not touch the developer's
         * real storage while RefreshDatabase runs the test suite.
         */
        if (app()->runningUnitTests()) {
            return;
        }

        $publicDisk =
            Storage::disk(
                'public',
            );

        $privateDisk =
            Storage::disk(
                'local',
            );

        $directories = [
            'customer-payment-uploads',
            'customer/payment-receipts',
        ];

        foreach ($directories as $directory) {
            $paths =
                $publicDisk->allFiles(
                    $directory,
                );

            foreach ($paths as $path) {
                $this->moveVerifiedFile(
                    $publicDisk,
                    $privateDisk,
                    $path,
                );
            }

            if (
                $publicDisk->allFiles(
                    $directory,
                )
                === []
            ) {
                $publicDisk->deleteDirectory(
                    $directory,
                );
            }
        }
    }

    /**
     * Privacy migrations intentionally do not restore sensitive
     * receipts to public storage during rollback.
     */
    public function down(): void
    {
        //
    }

    private function moveVerifiedFile(
        FilesystemAdapter $publicDisk,
        FilesystemAdapter $privateDisk,
        string $rawPath,
    ): void {
        $path =
            trim(
                str_replace(
                    '\\',
                    '/',
                    $rawPath,
                ),
                '/',
            );

        $unsafePath =
            $path === ''
            || str_contains(
                $path,
                "\0",
            )
            || str_contains(
                $path,
                '..',
            );

        if ($unsafePath) {
            throw new RuntimeException(
                "Unsafe legacy payment receipt path: {$rawPath}",
            );
        }

        if (
            ! $publicDisk->exists(
                $path,
            )
        ) {
            return;
        }

        $publicSize =
            $publicDisk->size(
                $path,
            );

        if (
            $privateDisk->exists(
                $path,
            )
        ) {
            $privateSize =
                $privateDisk->size(
                    $path,
                );

            if ($privateSize !== $publicSize) {
                throw new RuntimeException(
                    "Private receipt size mismatch: {$path}",
                );
            }

            if (
                ! $publicDisk->delete(
                    $path,
                )
            ) {
                throw new RuntimeException(
                    "Verified public receipt delete fail: {$path}",
                );
            }

            return;
        }

        $readStream =
            $publicDisk->readStream(
                $path,
            );

        if (! is_resource($readStream)) {
            throw new RuntimeException(
                "Public receipt read fail: {$path}",
            );
        }

        try {
            $written =
                $privateDisk->put(
                    $path,
                    $readStream,
                );
        } finally {
            fclose(
                $readStream,
            );
        }

        if ($written !== true) {
            throw new RuntimeException(
                "Private receipt write fail: {$path}",
            );
        }

        if (
            ! $privateDisk->exists(
                $path,
            )
        ) {
            throw new RuntimeException(
                "Private receipt verification fail: {$path}",
            );
        }

        $privateSize =
            $privateDisk->size(
                $path,
            );

        if ($privateSize !== $publicSize) {
            throw new RuntimeException(
                "Migrated receipt size mismatch: {$path}",
            );
        }

        if (
            ! $publicDisk->delete(
                $path,
            )
        ) {
            throw new RuntimeException(
                "Public receipt cleanup fail: {$path}",
            );
        }

        if (
            $publicDisk->exists(
                $path,
            )
        ) {
            throw new RuntimeException(
                "Public receipt still exists after cleanup: {$path}",
            );
        }
    }
};