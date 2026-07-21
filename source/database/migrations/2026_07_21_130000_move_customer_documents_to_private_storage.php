<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

return new class extends Migration
{
    public function up(): void
    {
        /*
         * RefreshDatabase testing migrations ko real project
         * filesystem modify karne ki permission nahi di jayegi.
         */
        if (
            app()->environment(
                'testing',
            )
        ) {
            return;
        }

        $paths = [];

        if (
            Schema::hasTable(
                'customer_documents',
            )
        ) {
            DB::table(
                'customer_documents',
            )
                ->select([
                    'id',
                    'file_path',
                    'documents',
                ])
                ->orderBy(
                    'id',
                )
                ->chunkById(
                    100,
                    function (
                        $records,
                    ) use (
                        &$paths,
                    ): void {
                        foreach (
                            $records
                            as $record
                        ) {
                            $legacyPath =
                                $this->safeCustomerDocumentPath(
                                    $record->file_path
                                    ?? null,
                                );

                            if (
                                $legacyPath
                                !== null
                            ) {
                                $paths[
                                    $legacyPath
                                ] = true;
                            }

                            $documents =
                                json_decode(
                                    (string) (
                                        $record->documents
                                        ?? ''
                                    ),
                                    true,
                                );

                            if (
                                ! is_array(
                                    $documents,
                                )
                            ) {
                                continue;
                            }

                            foreach (
                                $documents
                                as $document
                            ) {
                                if (
                                    ! is_array(
                                        $document,
                                    )
                                ) {
                                    continue;
                                }

                                $path =
                                    $this->safeCustomerDocumentPath(
                                        $document['file_path']
                                        ?? null,
                                    );

                                if (
                                    $path
                                    !== null
                                ) {
                                    $paths[
                                        $path
                                    ] = true;
                                }
                            }
                        }
                    },
                    'id',
                );
        }

        /*
         * Database se orphan ho chuki purani files bhi public
         * storage link se expose nahi rehni chahiye.
         */
        foreach (
            Storage::disk(
                'public',
            )->allFiles(
                'customer-documents',
            )
            as $publicPath
        ) {
            $path =
                $this->safeCustomerDocumentPath(
                    $publicPath,
                );

            if (
                $path
                !== null
            ) {
                $paths[
                    $path
                ] = true;
            }
        }

        foreach (
            array_keys(
                $paths,
            )
            as $path
        ) {
            $this->moveToPrivateDisk(
                $path,
            );
        }
    }

    public function down(): void
    {
        /*
         * Sensitive customer documents ko rollback mein
         * public storage mein dobara expose nahi kiya jayega.
         */
    }

    private function moveToPrivateDisk(
        string $path,
    ): void {
        $privateDisk =
            Storage::disk(
                'local',
            );

        $publicDisk =
            Storage::disk(
                'public',
            );

        $privateExists =
            $privateDisk->exists(
                $path,
            );

        $publicExists =
            $publicDisk->exists(
                $path,
            );

        if (
            $privateExists
            && ! $publicExists
        ) {
            return;
        }

        if (
            $privateExists
            && $publicExists
        ) {
            $privateSize =
                $privateDisk->size(
                    $path,
                );

            $publicSize =
                $publicDisk->size(
                    $path,
                );

            if (
                $privateSize
                !== $publicSize
            ) {
                throw new \RuntimeException(
                    "Customer document conflict detected: {$path}",
                );
            }

            $publicDisk->delete(
                $path,
            );

            return;
        }

        if (! $publicExists) {
            return;
        }

        $sourceStream =
            $publicDisk->readStream(
                $path,
            );

        if (
            ! is_resource(
                $sourceStream,
            )
        ) {
            throw new \RuntimeException(
                "Customer document read nahi hui: {$path}",
            );
        }

        try {
            $written =
                $privateDisk->writeStream(
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
            || ! $privateDisk->exists(
                $path,
            )
        ) {
            throw new \RuntimeException(
                "Customer document private storage mein move nahi hui: {$path}",
            );
        }

        $privateSize =
            $privateDisk->size(
                $path,
            );

        $publicSize =
            $publicDisk->size(
                $path,
            );

        if (
            $privateSize
            !== $publicSize
        ) {
            $privateDisk->delete(
                $path,
            );

            throw new \RuntimeException(
                "Customer document size verification fail hui: {$path}",
            );
        }

        $publicDisk->delete(
            $path,
        );
    }

    private function safeCustomerDocumentPath(
        mixed $filePath,
    ): ?string {
        $path =
            trim(
                str_replace(
                    '\\',
                    '/',
                    (string) $filePath,
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
            || ! str_starts_with(
                $path,
                'customer-documents/',
            )
        ) {
            return null;
        }

        return $path;
    }
};