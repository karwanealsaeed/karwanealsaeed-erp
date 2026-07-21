<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\TravelInsurancePayment;
use App\Services\TravelInsurancePrivateFileService;
use Illuminate\Support\Facades\Storage;

class TravelInsurancePaymentReceiptDownloadController extends Controller
{
    public function __invoke(
        TravelInsurancePayment $travelInsurancePayment,
    ) {
        abort_unless(
            auth()->user()?->hasPermission(
                'travel_insurance_receipts_download',
            ) ?? false,
            403,
        );

        $path =
            trim(
                (string) $travelInsurancePayment
                    ->receipt_path,
            );

        abort_unless(
            TravelInsurancePrivateFileService::isOwnedReceiptPath(
                $path,
            ),
            404,
        );

        $disk =
            Storage::disk(
                'local',
            );

        abort_unless(
            $disk->exists(
                $path,
            ),
            404,
        );

        $downloadName =
            basename(
                (string) (
                    $travelInsurancePayment
                        ->receipt_name
                    ?: $path
                ),
            );

        $mime =
            $travelInsurancePayment
                ->receipt_mime;

        if (blank($mime)) {
            $detectedMime =
                $disk->mimeType(
                    $path,
                );

            $mime =
                is_string(
                    $detectedMime,
                )
                    ? $detectedMime
                    : 'application/octet-stream';
        }

        return $disk->download(
            $path,
            $downloadName,
            [
                'Content-Type' =>
                    $mime,

                'X-Content-Type-Options' =>
                    'nosniff',

                'Cache-Control' =>
                    'private, no-store, max-age=0',
            ],
        );
    }
}