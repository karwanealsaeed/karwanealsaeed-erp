<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\TravelInsurancePayment;
use App\Models\TravelInsurancePolicy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CustomerPortalTravelInsuranceDownloadController extends Controller
{
    public function policyDocument(
        Request $request,
        TravelInsurancePolicy $travelInsurancePolicy,
    ): StreamedResponse {
        $customerId =
            $this->portalCustomerId(
                $request,
            );

        abort_unless(
            (int) $travelInsurancePolicy->customer_id
            === $customerId,
            403,
        );

        abort_unless(
            $travelInsurancePolicy->approval_status
            === 'Approved',
            404,
        );

        return $this->download(
            $travelInsurancePolicy->policy_document_path,
            $travelInsurancePolicy->policy_document_name,
        );
    }

    public function paymentReceipt(
        Request $request,
        TravelInsurancePayment $travelInsurancePayment,
    ): StreamedResponse {
        $customerId =
            $this->portalCustomerId(
                $request,
            );

        $travelInsurancePayment->loadMissing(
            'policy',
        );

        abort_unless(
            $travelInsurancePayment->policy,
            404,
        );

        abort_unless(
            (int) $travelInsurancePayment
                ->policy
                ->customer_id
            === $customerId,
            403,
        );

        abort_unless(
            $travelInsurancePayment->status
            === 'Approved',
            404,
        );

        return $this->download(
            $travelInsurancePayment->receipt_path,
            $travelInsurancePayment->receipt_name,
        );
    }

    private function portalCustomerId(
        Request $request,
    ): int {
        $customerId =
            (int) $request->session()
                ->get(
                    'customer_portal_id',
                    0,
                );

        abort_unless(
            $customerId > 0,
            403,
        );

        return $customerId;
    }

    private function download(
        mixed $storedPath,
        mixed $storedName,
    ): StreamedResponse {
        $path =
            trim(
                str_replace(
                    '\\',
                    '/',
                    (string) $storedPath,
                ),
            );

        $unsafe =
            $path === '';

        if (! $unsafe) {
            $unsafe =
                str_contains(
                    $path,
                    "\0",
                );
        }

        if (! $unsafe) {
            $unsafe =
                str_contains(
                    $path,
                    '..',
                );
        }

        if (! $unsafe) {
            $unsafe =
                str_starts_with(
                    $path,
                    '/',
                );
        }

        if (! $unsafe) {
            $unsafe =
                preg_match(
                    '/^[A-Za-z]:/',
                    $path,
                ) === 1;
        }

        if (! $unsafe) {
            $unsafe =
                ! str_starts_with(
                    $path,
                    'travel-insurance/',
                );
        }

        abort_if(
            $unsafe,
            404,
        );

        abort_unless(
            Storage::disk(
                'local',
            )->exists(
                $path,
            ),
            404,
        );

        $fileName =
            trim(
                (string) $storedName,
            );

        if ($fileName === '') {
            $fileName =
                basename(
                    $path,
                );
        }

        $fileName =
            basename(
                str_replace(
                    [
                        '\\',
                        '/',
                    ],
                    '-',
                    $fileName,
                ),
            );

        $fileName =
            preg_replace(
                '/[^A-Za-z0-9._ -]/',
                '-',
                $fileName,
            ) ?: 'insurance-file';

        return Storage::disk(
            'local',
        )->download(
            $path,
            $fileName,
            [
                'Cache-Control' =>
                    'private, no-store, max-age=0',

                'Pragma' =>
                    'no-cache',

                'X-Content-Type-Options' =>
                    'nosniff',
            ],
        );
    }
}