<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\TravelInsurancePolicy;
use App\Services\TravelInsurancePrivateFileService;
use Illuminate\Support\Facades\Storage;

class TravelInsurancePolicyDocumentDownloadController extends Controller
{
    public function __invoke(
        TravelInsurancePolicy $travelInsurancePolicy,
    ) {
        abort_unless(
            auth()->user()?->hasPermission(
                'travel_insurance_documents_download',
            ) ?? false,
            403,
        );

        $path =
            trim(
                (string) $travelInsurancePolicy
                    ->policy_document_path,
            );

        abort_unless(
            TravelInsurancePrivateFileService::isOwnedPolicyPath(
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
                    $travelInsurancePolicy
                        ->policy_document_name
                    ?: $path
                ),
            );

        $mime =
            $travelInsurancePolicy
                ->policy_document_mime;

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