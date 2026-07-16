<?php

namespace App\Http\Controllers;

use App\Services\UnifiedCustomerStatementService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;

class CustomerStatementPdfController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $validated = $request->validate([
            'customer' => [
                'required',
                'integer',
                'exists:customers,id',
            ],

            'date_from' => [
                'required',
                'date_format:Y-m-d',
            ],

            'date_to' => [
                'required',
                'date_format:Y-m-d',
            ],

            'service_type' => [
                'required',
                'string',
                'max:50',
            ],

            'disposition' => [
                'nullable',
                'in:inline,download',
            ],
        ]);

        $report = app(
            UnifiedCustomerStatementService::class
        )->generate(
            customerId: (int) $validated['customer'],
            dateFrom: $validated['date_from'],
            dateTo: $validated['date_to'],
            serviceType: $validated['service_type'],
        );

        abort_unless(
            $report['database_ready']
            && ! $report['customer_required']
            && filled($report['customer']),
            404
        );

        $customerName = (string) (
            $report['customer']['name']
            ?? 'customer'
        );

        $safeName = Str::slug($customerName);

        if (blank($safeName)) {
            $safeName = 'customer';
        }

        $fileName = 'customer-statement-'
            . $safeName
            . '-'
            . now()->format('Ymd-His')
            . '.pdf';

        $pdf = Pdf::loadView(
            'pdf.customer-statement',
            [
                'report' => $report,

                'company' => config(
                    'customer_statement.company',
                    []
                ),
            ]
        )
            ->setPaper('a4', 'landscape')
            ->setOptions(
                [
                    'defaultFont' => 'DejaVu Sans',
                    'isRemoteEnabled' => false,
                    'isPhpEnabled' => false,
                    'isJavascriptEnabled' => false,
                    'isHtml5ParserEnabled' => true,
                    'dpi' => 96,
                ],
                mergeWithDefaults: true
            )
            ->setWarnings(false);

        $disposition = $validated['disposition']
            ?? 'inline';

        if ($disposition === 'download') {
            return $pdf->download($fileName);
        }

        return $pdf->stream($fileName);
    }
}