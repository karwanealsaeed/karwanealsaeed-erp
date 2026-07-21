<?php

namespace App\Http\Controllers;

use App\Models\CustomerPaymentUpload;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CustomerPaymentReceiptDownloadController extends Controller
{
    public function __invoke(
        Request $request,
        CustomerPaymentUpload $customerPaymentUpload,
    ): StreamedResponse {
        abort_unless(
            (int) $request->session()
                ->get(
                    'customer_portal_id',
                    0,
                )
            === (int) $customerPaymentUpload
                ->customer_id,
            403,
        );

        $path =
            trim(
                str_replace(
                    '\\',
                    '/',
                    (string) $customerPaymentUpload
                        ->receipt_file,
                ),
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
            )
            || str_starts_with(
                $path,
                '/',
            );

        abort_if(
            $unsafePath,
            404,
        );

        $diskName =
            Storage::disk(
                'local',
            )->exists(
                $path,
            )
                ? 'local'
                : 'public';

        abort_unless(
            Storage::disk(
                $diskName,
            )->exists(
                $path,
            ),
            404,
        );

        $extension =
            strtolower(
                pathinfo(
                    $path,
                    PATHINFO_EXTENSION,
                ),
            );

        $safeUploadNo =
            preg_replace(
                '/[^A-Za-z0-9_-]+/',
                '-',
                (string) $customerPaymentUpload
                    ->upload_no,
            )
            ?: 'payment-receipt';

        $fileName =
            $safeUploadNo
            .(
                $extension !== ''
                    ? '.'.$extension
                    : ''
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
                $path,
                $fileName,
                $headers,
            );
        }

        return Storage::disk(
            $diskName,
        )->download(
            $path,
            $fileName,
            $headers,
        );
    }
}