<?php

namespace App\Http\Controllers;

use App\Models\CustomerDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use ZipArchive;

class CustomerDocumentDownloadController extends Controller
{
    public function __invoke(
        Request $request,
        CustomerDocument $customerDocument,
        string $fileIndex = '0',
    ) {
        $this->authorizeAccess(
            $request,
            $customerDocument,
        );

        if ($fileIndex === 'all') {
            return $this->downloadAll(
                $customerDocument,
            );
        }

        $documents =
            $customerDocument->documents_list;

        $document =
            $documents[
                (int) $fileIndex
            ]
            ?? null;

        abort_unless(
            is_array(
                $document,
            ),
            404,
        );

        $filePath =
            $this->safeFilePath(
                $document['file_path']
                ?? null,
            );

        $diskName =
            $this->resolveDiskName(
                $filePath,
            );

        $fileName =
            $this->safeFileName(
                $document['file_name']
                ?? basename(
                    $filePath,
                ),
                'customer-document',
            );

        $headers = [
            'Cache-Control' =>
                'private, no-store, max-age=0',

            'Pragma' =>
                'no-cache',

            'X-Content-Type-Options' =>
                'nosniff',
        ];

        if (
            $request->boolean(
                'view',
            )
        ) {
            return Storage::disk(
                $diskName,
            )->response(
                $filePath,
                $fileName,
                $headers,
            );
        }

        return Storage::disk(
            $diskName,
        )->download(
            $filePath,
            $fileName,
            $headers,
        );
    }

    private function authorizeAccess(
        Request $request,
        CustomerDocument $customerDocument,
    ): void {
        if (
            $request->routeIs(
                'customer.portal.documents.download',
            )
        ) {
            abort_unless(
                (int) $request->session()
                    ->get(
                        'customer_portal_id',
                        0,
                    )
                === (int) $customerDocument
                    ->customer_id,
                403,
            );

            return;
        }

        abort_unless(
            $request->user()
                ?->hasPermission(
                    'customer_documents.download',
                )
            ?? false,
            403,
        );
    }

    private function downloadAll(
        CustomerDocument $customerDocument,
    ) {
        abort_unless(
            class_exists(
                ZipArchive::class,
            ),
            500,
            'PHP Zip extension enabled nahi hai.',
        );

        $documents =
            $customerDocument->documents_list;

        abort_unless(
            count(
                $documents,
            )
            > 0,
            404,
        );

        $tempDirectory =
            storage_path(
                'app/temp',
            );

        if (
            ! is_dir(
                $tempDirectory,
            )
        ) {
            $created =
                mkdir(
                    $tempDirectory,
                    0755,
                    true,
                );

            abort_unless(
                $created
                || is_dir(
                    $tempDirectory,
                ),
                500,
                'Temporary document directory create nahi hui.',
            );
        }

        $safeCustomerName =
            $this->safeFileName(
                $customerDocument->customer
                    ?->name
                ?: 'customer',
                'customer',
            );

        $zipFileName =
            'customer-documents-'
            .$safeCustomerName
            .'-'
            .$customerDocument->id
            .'.zip';

        $zipPath =
            $tempDirectory
            .DIRECTORY_SEPARATOR
            .$zipFileName;

        if (
            file_exists(
                $zipPath,
            )
        ) {
            unlink(
                $zipPath,
            );
        }

        $zip =
            new ZipArchive();

        abort_unless(
            $zip->open(
                $zipPath,
                ZipArchive::CREATE
                | ZipArchive::OVERWRITE,
            )
            === true,
            500,
            'ZIP file create nahi ho saki.',
        );

        $addedFiles = 0;

        foreach (
            $documents
            as $index => $document
        ) {
            if (
                ! is_array(
                    $document,
                )
            ) {
                continue;
            }

            try {
                $filePath =
                    $this->safeFilePath(
                        $document['file_path']
                        ?? null,
                    );

                $diskName =
                    $this->resolveDiskName(
                        $filePath,
                    );
            } catch (\Symfony\Component\HttpKernel\Exception\HttpException) {
                continue;
            }

            $fileName =
                $this->safeFileName(
                    $document['file_name']
                    ?? basename(
                        $filePath,
                    ),
                    'document-'
                    .(
                        $index
                        + 1
                    ),
                );

            $added =
                $zip->addFile(
                    Storage::disk(
                        $diskName,
                    )->path(
                        $filePath,
                    ),
                    (
                        $index
                        + 1
                    )
                    .'-'
                    .$fileName,
                );

            if ($added) {
                $addedFiles++;
            }
        }

        $zip->close();

        if ($addedFiles < 1) {
            if (
                file_exists(
                    $zipPath,
                )
            ) {
                unlink(
                    $zipPath,
                );
            }

            abort(
                404,
            );
        }

        abort_unless(
            file_exists(
                $zipPath,
            ),
            404,
        );

        return response()
            ->download(
                $zipPath,
                $zipFileName,
                [
                    'Cache-Control' =>
                        'private, no-store, max-age=0',

                    'Pragma' =>
                        'no-cache',

                    'X-Content-Type-Options' =>
                        'nosniff',
                ],
            )
            ->deleteFileAfterSend(
                true,
            );
    }

    private function resolveDiskName(
        string $filePath,
    ): string {
        if (
            Storage::disk(
                'local',
            )->exists(
                $filePath,
            )
        ) {
            return 'local';
        }

        if (
            Storage::disk(
                'public',
            )->exists(
                $filePath,
            )
        ) {
            return 'public';
        }

        abort(
            404,
        );
    }

    private function safeFilePath(
        mixed $filePath,
    ): string {
        $path =
            trim(
                str_replace(
                    '\\',
                    '/',
                    (string) $filePath,
                ),
            );

        $unsafe =
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
            );

        abort_if(
            $unsafe,
            404,
        );

        return $path;
    }

    private function safeFileName(
        mixed $fileName,
        string $fallback,
    ): string {
        $name =
            basename(
                str_replace(
                    '\\',
                    '/',
                    trim(
                        (string) $fileName,
                    ),
                ),
            );

        $name =
            preg_replace(
                '/[^A-Za-z0-9._-]+/',
                '-',
                $name,
            )
            ?: '';

        return trim(
            $name,
            '.-',
        )
        ?: $fallback;
    }
}