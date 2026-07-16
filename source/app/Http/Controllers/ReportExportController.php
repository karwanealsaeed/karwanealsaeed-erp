<?php

namespace App\Http\Controllers;

use App\Filament\Pages\AirlineWiseFlightReport;
use App\Filament\Pages\BookingStatusReport;
use App\Filament\Pages\CountryWiseVisaReport;
use App\Filament\Pages\CustomerLedgerReport;
use App\Filament\Pages\CustomerWiseSalesReport;
use App\Filament\Pages\DailyCashbookReport;
use App\Filament\Pages\DailyFinanceReport;
use App\Filament\Pages\DateRangeFinanceReport;
use App\Filament\Pages\ExpenseCategoryReport;
use App\Filament\Pages\HotelWiseReport;
use App\Filament\Pages\MonthlyFinanceReport;
use App\Filament\Pages\OutstandingBalanceReport;
use App\Filament\Pages\PassportStatusReport;
use App\Filament\Pages\PaymentMethodReport;
use App\Filament\Pages\RefundReport;
use App\Filament\Pages\RouteWiseFlightReport;
use App\Filament\Pages\ServiceWiseSalesReport;
use App\Filament\Pages\UmrahPackageWiseReport;
use App\Filament\Pages\VisaTypeWiseReport;
use App\Filament\Pages\YearlyFinanceReport;
use App\Models\CompanySetting;
use Barryvdh\DomPDF\Facade\Pdf;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ReportExportController extends Controller
{
    private array $reports = [
        'daily-finance-report' => [
            'title' => 'Daily Finance Report',
            'class' => DailyFinanceReport::class,
        ],
        'monthly-finance-report' => [
            'title' => 'Monthly Finance Report',
            'class' => MonthlyFinanceReport::class,
        ],
        'yearly-finance-report' => [
            'title' => 'Yearly Finance Report',
            'class' => YearlyFinanceReport::class,
        ],
        'date-range-finance-report' => [
            'title' => 'Date Range Finance Report',
            'class' => DateRangeFinanceReport::class,
        ],
        'daily-cashbook-report' => [
            'title' => 'Daily Cashbook Report',
            'class' => DailyCashbookReport::class,
        ],
        'service-wise-sales-report' => [
            'title' => 'Service Wise Sales Report',
            'class' => ServiceWiseSalesReport::class,
        ],
        'customer-wise-sales-report' => [
            'title' => 'Customer Wise Sales Report',
            'class' => CustomerWiseSalesReport::class,
        ],
        'customer-ledger-report' => [
            'title' => 'Customer Ledger Report',
            'class' => CustomerLedgerReport::class,
        ],
        'payment-method-report' => [
            'title' => 'Payment Method Report',
            'class' => PaymentMethodReport::class,
        ],
        'expense-category-report' => [
            'title' => 'Expense Category Report',
            'class' => ExpenseCategoryReport::class,
        ],
        'refund-report' => [
            'title' => 'Refund Report',
            'class' => RefundReport::class,
        ],
        'outstanding-balance-report' => [
            'title' => 'Outstanding Balance Report',
            'class' => OutstandingBalanceReport::class,
        ],
        'booking-status-report' => [
            'title' => 'Booking Status Report',
            'class' => BookingStatusReport::class,
        ],
        'country-wise-visa-report' => [
            'title' => 'Country Wise Visa Report',
            'class' => CountryWiseVisaReport::class,
        ],
        'visa-type-wise-report' => [
            'title' => 'Visa Type Wise Report',
            'class' => VisaTypeWiseReport::class,
        ],
        'passport-status-report' => [
            'title' => 'Passport Status Report',
            'class' => PassportStatusReport::class,
        ],
        'airline-wise-flight-report' => [
            'title' => 'Airline Wise Flight Report',
            'class' => AirlineWiseFlightReport::class,
        ],
        'route-wise-flight-report' => [
            'title' => 'Route Wise Flight Report',
            'class' => RouteWiseFlightReport::class,
        ],
        'umrah-package-wise-report' => [
            'title' => 'Umrah Package Wise Report',
            'class' => UmrahPackageWiseReport::class,
        ],
        'hotel-wise-report' => [
            'title' => 'Hotel Wise Report',
            'class' => HotelWiseReport::class,
        ],
    ];

    public function __invoke(Request $request, string $report, string $format)
    {
        abort_unless(isset($this->reports[$report]), 404);
        abort_unless($request->user()?->canViewReports() ?? false, 403);

if ($format === 'print') {
    abort_unless($request->user()?->canPrintReports() ?? false, 403);
}

if ($format === 'pdf') {
    abort_unless($request->user()?->canDownloadReportPdf() ?? false, 403);
}

if ($format === 'excel') {
    abort_unless($request->user()?->canDownloadReportExcel() ?? false, 403);
}

        $config = $this->reports[$report];

        $page = app($config['class']);

        if (method_exists($page, 'mount')) {
            $page->mount();
        }

        foreach ([
            'reportDate',
            'reportMonth',
            'reportYear',
            'fromDate',
            'toDate',
            'customerId',
            'passportStatus',
        ] as $property) {
            if ($request->has($property) && property_exists($page, $property)) {
                $value = $request->input($property);
                $page->{$property} = $value === '' ? null : $value;
            }
        }

        $data = $page->getReportData();

        $company = CompanySetting::current();
        $logoSrc = $this->getCompanyLogoSrc($company);

        $exportData = $this->prepareExportData($data);

        $title = $config['title'];
        $fileName = Str::slug($title) . '-' . now()->format('Ymd-His');

        if ($format === 'print') {
            return view('reports.export', [
                'title' => $title,
                'company' => $company,
                'logoSrc' => $logoSrc,
                'exportData' => $exportData,
                'generatedAt' => now()->format('d M Y h:i A'),
                'isPrint' => true,
            ]);
        }

        if ($format === 'pdf') {
            $pdf = Pdf::loadView('reports.export', [
                'title' => $title,
                'company' => $company,
                'logoSrc' => $logoSrc,
                'exportData' => $exportData,
                'generatedAt' => now()->format('d M Y h:i A'),
                'isPrint' => false,
            ])->setPaper('a4', 'landscape');

            return $pdf->download($fileName . '.pdf');
        }

        $html = view('reports.export', [
            'title' => $title,
            'company' => $company,
            'logoSrc' => $logoSrc,
            'exportData' => $exportData,
            'generatedAt' => now()->format('d M Y h:i A'),
            'isPrint' => false,
        ])->render();

        return response("\xEF\xBB\xBF" . $html, 200, [
            'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '.xls"',
            'Cache-Control' => 'max-age=0',
        ]);
    }

    private function getCompanyLogoSrc(CompanySetting $company): ?string
    {
        if (! $company->logo) {
            return null;
        }

        $path = public_path('storage/' . $company->logo);

        if (! file_exists($path)) {
            return null;
        }

        $mime = mime_content_type($path) ?: 'image/png';
        $base64 = base64_encode(file_get_contents($path));

        return 'data:' . $mime . ';base64,' . $base64;
    }

    private function prepareExportData(array $data): array
    {
        $summary = [];
        $tables = [];

        foreach ($data as $key => $value) {
            if ($key === 'customer' && $value) {
                $tables[] = $this->makeTable('Customer Information', [[
                    'name' => $value->name ?? '-',
                    'mobile' => $value->mobile ?? '-',
                    'cnic' => $value->cnic ?? '-',
                    'passport_no' => $value->passport_no ?? '-',
                    'email' => $value->email ?? '-',
                    'address' => $value->address ?? '-',
                ]]);

                continue;
            }

            if (in_array($key, ['rows', 'transactions', 'service_rows', 'status_rows'], true) && is_array($value)) {
                $tables[] = $this->makeTable($this->label($key), $value);

                continue;
            }

            if ($this->isSimpleValue($value)) {
                $summary[$this->label($key)] = $this->formatValue($value);
            }
        }

        return [
            'summary' => $summary,
            'tables' => $tables,
        ];
    }

    private function makeTable(string $title, array $rows): array
    {
        $formattedRows = [];

        foreach ($rows as $row) {
            if ($row instanceof Model) {
                $row = $row->toArray();
            }

            if (is_object($row) && method_exists($row, 'toArray')) {
                $row = $row->toArray();
            }

            if (! is_array($row)) {
                continue;
            }

            $formattedRow = [];

            foreach ($row as $key => $value) {
                if (is_array($value) || (is_object($value) && ! $value instanceof DateTimeInterface)) {
                    continue;
                }

                $formattedRow[$this->label($key)] = $this->formatValue($value);
            }

            $formattedRows[] = $formattedRow;
        }

        return [
            'title' => $title,
            'rows' => $formattedRows,
        ];
    }

    private function isSimpleValue(mixed $value): bool
    {
        return is_null($value)
            || is_string($value)
            || is_int($value)
            || is_float($value)
            || is_bool($value)
            || $value instanceof DateTimeInterface;
    }

    private function formatValue(mixed $value): string
    {
        if ($value instanceof DateTimeInterface) {
            return $value->format('d M Y');
        }

        if (is_bool($value)) {
            return $value ? 'Yes' : 'No';
        }

        if (is_float($value)) {
            return number_format($value, 2);
        }

        if (is_int($value)) {
            return number_format($value);
        }

        if ($value === null || $value === '') {
            return '-';
        }

        return (string) $value;
    }

    private function label(string $key): string
    {
        return (string) Str::of($key)
            ->replace('_', ' ')
            ->title();
    }
}