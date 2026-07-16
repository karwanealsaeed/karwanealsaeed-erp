<?php

namespace App\Http\Controllers;

use App\Services\UnifiedSupplierStatementService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SupplierStatementPdfController extends Controller
{
    public function __invoke(Request $request)
    {
        $validated = $request->validate([
            'supplier' => [
                'required',
                'integer',
                'exists:suppliers,id',
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
            UnifiedSupplierStatementService::class
        )->generate(
            supplierId: (int) $validated['supplier'],
            dateFrom: $validated['date_from'],
            dateTo: $validated['date_to'],
            serviceType: $validated['service_type'],
        );

        abort_unless(
            $report['database_ready']
            && ! $report['supplier_required']
            && filled($report['supplier']),
            404
        );

        $supplierName = (string) (
            $report['supplier']['name']
            ?? 'supplier'
        );

        $safeName = Str::slug($supplierName);

        if (blank($safeName)) {
            $safeName = 'supplier';
        }

        $fileName = 'supplier-statement-'
            . $safeName
            . '-'
            . now()->format('Ymd-His')
            . '.pdf';

        $pdf = Pdf::loadView(
            'pdf.supplier-statement',
            [
                'report' => $report,

                'company' => config(
                    'customer_statement.company',
                    []
                ),
            ]
        )
            ->setPaper('a4', 'landscape')
            ->setOptions([
                'defaultFont' => 'DejaVu Sans',
                'isRemoteEnabled' => false,
                'isPhpEnabled' => false,
                'isJavascriptEnabled' => false,
                'isHtml5ParserEnabled' => true,
                'dpi' => 96,
            ])
            ->setWarnings(false);

        if (
            ($validated['disposition'] ?? 'inline')
            === 'download'
        ) {
            return $pdf->download($fileName);
        }

        return $pdf->stream($fileName);
    }
}