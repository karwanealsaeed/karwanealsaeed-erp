<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\TravelInsuranceReportService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TravelInsuranceReportExportController extends Controller
{
    public function __invoke(
        Request $request,
        string $format,
        TravelInsuranceReportService $service,
    ): Response | StreamedResponse {
        abort_unless(
            in_array(
                $format,
                [
                    'print',
                    'pdf',
                    'excel',
                ],
                true,
            ),
            404,
        );

        $user =
            $request->user();

        abort_unless(
            $user?->canViewReports()
            ?? false,
            403,
        );

        $allowed =
            match ($format) {
                'print' =>
                    $user->canPrintReports(),

                'pdf' =>
                    $user->canDownloadReportPdf(),

                'excel' =>
                    $user->canDownloadReportExcel(),

                default =>
                    false,
            };

        abort_unless(
            $allowed,
            403,
        );

        $filters =
            $service->normalizeFilters(
                $request->query(),
            );

        $query =
            $service->query(
                $filters,
            );

        if ($format === 'excel') {
            return $this->excel(
                $service,
                $query,
            );
        }

        $limit =
            $format === 'pdf'
                ? 1500
                : 5000;

        $summary =
            $service->summary(
                clone $query,
            );

        $rows =
            $service->rows(
                clone $query,
                $limit,
            );

        $data = [
            'filters' =>
                $filters,

            'summary' =>
                $summary,

            'rows' =>
                $rows,

            'truncated' =>
                $summary['policy_count']
                > $rows->count(),

            'isPdf' =>
                $format === 'pdf',
        ];

        $fileName =
            'travel-insurance-report-'
            .now()->format(
                'Ymd-His',
            );

        if ($format === 'pdf') {
            return Pdf::loadView(
                'reports.travel-insurance',
                $data,
            )
                ->setPaper(
                    'a4',
                    'landscape',
                )
                ->download(
                    "{$fileName}.pdf",
                );
        }

        return response()
            ->view(
                'reports.travel-insurance',
                $data,
            )
            ->header(
                'Cache-Control',
                'private, no-store, max-age=0',
            )
            ->header(
                'Pragma',
                'no-cache',
            )
            ->header(
                'X-Content-Type-Options',
                'nosniff',
            );
    }

    private function excel(
        TravelInsuranceReportService $service,
        $query,
    ): StreamedResponse {
        $fileName =
            'travel-insurance-report-'
            .now()->format(
                'Ymd-His',
            )
            .'.csv';

        return response()->streamDownload(
            function () use (
                $service,
                $query,
            ): void {
                $handle =
                    fopen(
                        'php://output',
                        'wb',
                    );

                if ($handle === false) {
                    return;
                }

                fwrite(
                    $handle,
                    "\xEF\xBB\xBF",
                );

                fputcsv(
                    $handle,
                    $service->spreadsheetHeadings(),
                    ',',
                    '"',
                    '\\',
                    "\r\n",
                );

                $query
                    ->reorder()
                    ->orderBy(
                        'id',
                    )
                    ->chunkById(
                        500,
                        function (
                            $policies,
                        ) use (
                            $handle,
                            $service,
                        ): void {
                            foreach ($policies as $policy) {
                                fputcsv(
                                    $handle,
                                    $service->spreadsheetRow(
                                        $policy,
                                    ),
                                    ',',
                                    '"',
                                    '\\',
                                    "\r\n",
                                );
                            }
                        },
                    );

                fclose(
                    $handle,
                );
            },
            $fileName,
            [
                'Content-Type' =>
                    'application/vnd.ms-excel; charset=UTF-8',

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